<?php

namespace Webkul\Reel\GraphQL\Mutations;

use Webkul\Reel\Models\Reel;
use Webkul\Reel\Models\ReelLike;
use Webkul\Reel\Models\ReelView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ReelInteractionMutation
{
    /**
     * Like/Unlike a reel
     */
    public function like($root, array $args, GraphQLContext $context)
    {
        try {
            $id = $args['id'];
            $reel = Reel::findOrFail($id);

            // Check customer authentication
            $customer = auth()->guard('customer')->user();
            if (!$customer) {
                // Try other possible guards
                $customer = auth()->guard('api')->user();
            }

            // // Check admin authentication
            $admin = auth()->guard('admin')->user();
            if (!$admin) {
                // Try other possible admin guards
                $admin = auth()->guard('admin-api')->user();
            }

            if (!$customer && !$admin) {
                throw new \Exception('Authentication required to like a reel. Please login as customer or admin.');
            }

            $userId = $customer ? $customer->id : $admin->id;
            $userType = $customer ? 'customer' : 'admin';

            // Check if already liked
            $existingLike = ReelLike::where([
                'reel_id' => $reel->id,
                'customer_id' => $userId,
            ])->first();

            DB::beginTransaction();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                $reel->decrement('likes_count');
                $message = 'Reel unliked successfully.';
                $liked = false;
            } else {
                // Like
                ReelLike::create([
                    'reel_id' => $reel->id,
                    'customer_id' => $userId,
                ]);
                $reel->increment('likes_count');
                $message = 'Reel liked successfully.';
                $liked = true;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $message,
                'reel' => $reel->fresh(),
                'liked' => $liked,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Like reel error:', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel' => null,
                'liked' => null,
            ];
        }
    }

    /**
     * View a reel
     */
    public function view($root, array $args, GraphQLContext $context)
    {
        try {
            $id = $args['id'];
            $reel = Reel::findOrFail($id);
            $request = app('request');

            // Get customer ID if authenticated
            $customerId = null;

            // Check customer authentication
            $customer = auth()->guard('customer')->user();
            if (!$customer) {
                // Try other possible guards
                $customer = auth()->guard('api')->user();
            }

            // // Check admin authentication
            $admin = auth()->guard('admin')->user();
            if (!$admin) {
                // Try other possible admin guards
                $admin = auth()->guard('admin-api')->user();
            }

            if (!$customer && !$admin) {
                throw new \Exception('Authentication required to like a reel. Please login as customer or admin.');
            }

            $customerId = $customer ? $customer->id : $admin->id;
            $userType = $customer ? 'customer' : 'admin';

            // Check if this view should be counted (prevent duplicate views within 1 hour)
            $viewExists = ReelView::where('reel_id', $reel->id)
                ->where(function ($query) use ($customerId, $request) {
                    if ($customerId) {
                        $query->where('customer_id', $customerId);
                    } else {
                        // For guests, check IP and session
                        $query->where('ip_address', $request->ip())
                            ->where('session_id', session()->getId());
                    }
                })
                ->where('created_at', '>=', now()->subHour())
                ->first();

            if (!$viewExists) {
                DB::beginTransaction();

                // Record view
                ReelView::create([
                    'reel_id' => $reel->id,
                    'customer_id' => $customerId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                ]);

                // Increment view count
                $reel->increment('views_count');

                DB::commit();
            }

            return [
                'success' => true,
                'message' => 'View recorded successfully.',
                'reel' => $reel->fresh(),
                'views_count' => $reel->fresh()->views_count,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('View reel error:', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel' => null,
                'views_count' => null,
            ];
        }
    }

    /**
     * Get reel analytics (Admin only)
     */
    public function analytics($root, array $args, GraphQLContext $context)
    {
        try {
            $id = $args['id'];
            $reel = Reel::findOrFail($id);

            // Check customer authentication
            $customer = auth()->guard('customer')->user();
            if (!$customer) {
                // Try other possible guards
                $customer = auth()->guard('api')->user();
            }

            // // Check admin authentication
            $admin = auth()->guard('admin')->user();
            if (!$admin) {
                // Try other possible admin guards
                $admin = auth()->guard('admin-api')->user();
            }

            if (!$customer && !$admin) {
                throw new \Exception('Authentication required to like a reel. Please login as customer or admin.');
            }

            $userId = $customer ? $customer->id : $admin->id;
            $userType = $customer ? 'customer' : 'admin';

            // Check if user is admin (only admins can see analytics)
            if ($userType != 'admin') {
                throw new \Exception('Unauthorized. Only administrators can view analytics.');
            }

            // Get likes count
            $likes = ReelLike::where('reel_id', $reel->id)->count();

            // Get views count
            $views = ReelView::where('reel_id', $reel->id)->count();

            // Get unique viewers
            $uniqueViewers = ReelView::where('reel_id', $reel->id)
                ->select(DB::raw('COUNT(DISTINCT COALESCE(customer_id, ip_address, session_id)) as unique_count'))
                ->value('unique_count') ?? 0;

            return [
                'success' => true,
                'message' => 'Analytics fetched successfully.',
                'reel' => $reel,
                'analytics' => [
                    'total_likes' => $likes,
                    'total_views' => $views,
                    'unique_viewers' => $uniqueViewers,
                    'engagement_rate' => $views > 0 ? round(($likes / $views) * 100, 2) : 0,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Reel analytics error:', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel' => null,
                'analytics' => null,
            ];
        }
    }
}
