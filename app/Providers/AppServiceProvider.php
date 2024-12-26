<?php

namespace App\Providers;

use Laravel\Cashier\Cashier;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use App\Models\User;

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
        if (App::environment(['production'])) {
            URL::forceScheme('https');
        }
        Paginator::useBootstrap();

        // Cashier に使用する顧客モデルを指定
        Cashier::useCustomerModel(User::class);
    }
}
