<?php

namespace Webkul\Product\Providers;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Webkul\Product\GraphQL\Queries\ProductByAdditionalDataQuery;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // This service provider is intentionally left empty
        // as Bagisto's GraphQL schema is already loaded through the main GraphQL package
        $this->app['events']->listen(BuildSchemaString::class, function (BuildSchemaString $event) {
            $schemaPath = __DIR__ . '/../graphql/schema.graphql';

            if (file_exists($schemaPath)) {
                return file_get_contents($schemaPath);
            }

            return '';
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge our Product GraphQL schema with the main schema
        $this->app->extend(SchemaSourceProvider::class, function ($originalProvider, $app) {
            return new class($originalProvider) implements SchemaSourceProvider {
                protected $originalProvider;

                public function __construct($originalProvider)
                {
                    $this->originalProvider = $originalProvider;
                }

                public function getSchemaString(): string
                {
                    // Get the original schema
                    $originalSchema = $this->originalProvider->getSchemaString();

                    // Get our Product schema
                    $productSchemaPath = __DIR__ . '/../../GraphQL/schema.graphql';

                    if (file_exists($productSchemaPath)) {
                        $productSchema = file_get_contents($productSchemaPath);
                        return $originalSchema . "\n" . $productSchema;
                    }

                    return $originalSchema;
                }
            };
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
}
