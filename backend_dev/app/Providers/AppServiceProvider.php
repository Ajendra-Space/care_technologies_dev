<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
        Schema::defaultStringLength(191);
        
        // Create storage directories if they don't exist
        if (!Storage::disk('public')->exists('contacts/profile_images')) {
            Storage::disk('public')->makeDirectory('contacts/profile_images');
        }
        if (!Storage::disk('public')->exists('contacts/files')) {
            Storage::disk('public')->makeDirectory('contacts/files');
        }
    }
}