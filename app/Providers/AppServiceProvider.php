<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

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
        //clear cache when updated or deleted User or Post
        Post::updated(function () {
            Cache::forget('stats_data');
        });

        Post::deleted(function () {
            Cache::forget('stats_data');
        });

        // Clear cache when a User model is updated
        User::updated(function () {
            Cache::forget('stats_data');
        });

        User::deleted(function () {
            Cache::forget('stats_data');
        });
    }
}
