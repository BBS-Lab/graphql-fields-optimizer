<?php

namespace BBSLab\GraphqlFieldsOptimizer\Providers;

use Illuminate\Support\ServiceProvider;

class GraphqlFieldsOptimizerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/graphql-fields-optimizer.php' => config_path('graphql-fields-optimizer.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/graphql-fields-optimizer.php', 'graphql-fields-optimizer');
    }
}