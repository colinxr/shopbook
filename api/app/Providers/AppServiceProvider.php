<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 * 
 * This class registers application services and configures global behavior.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure UUID as the default primary key type
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        // Configure JSON API response format for Laravel's built-in validation
        $this->configureJsonApiValidation();
    }

    /**
     * Configure JSON API validation response format.
     */
    private function configureJsonApiValidation(): void
    {
        // Override the default validation response format for API requests
        \Illuminate\Support\Facades\Response::macro('validationError', function ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid',
                'errors' => $errors
            ], 422);
        });
    }
}
