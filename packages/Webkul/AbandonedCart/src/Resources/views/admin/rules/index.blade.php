{{-- packages/Webkul/AbandonedCart/src/Resources/views/admin/rules/index.blade.php --}}
<x-admin::layouts>

    <x-slot:title>
        Abandoned Cart Rules
    </x-slot:title>

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
            Abandoned Cart Rules
        </h1>

        <a href="{{ route('admin.abandoned_cart.rules.create') }}" class="primary-button">
            Create Rule
        </a>
    </div>

    <div class="bg-white rounded dark:bg-gray-900 box-shadow">
        <div class="overflow-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            ID
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Name
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Status
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Abandoned After
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Send After
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Max Reminders
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-left text-gray-600 dark:text-gray-300">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            #{{ $rule->id }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">
                            {{ $rule->name }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $rule->status == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($rule->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ formatMinutes($rule->abandoned_after_minutes) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ formatMinutes($rule->send_after_minutes) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ $rule->max_reminders }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.abandoned_cart.rules.edit', $rule->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="Edit">
                                    <span class="icon-edit"></span>
                                </a>

                                <form action="{{ route('admin.abandoned_cart.rules.delete', $rule->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                        <span class="icon-delete"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No rules found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin::layouts>

@php
function formatMinutes($minutes) {
if ($minutes < 60) { return $minutes . ' min' ; } elseif ($minutes < 1440) { $hours=floor($minutes / 60); return $hours . ' hour' . ($hours> 1 ? 's' : '');
    } else {
    $days = floor($minutes / 1440);
    return $days . ' day' . ($days > 1 ? 's' : '');
    }
    }
    @endphp
