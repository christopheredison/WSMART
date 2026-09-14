<?php

namespace App\Providers;

use App\Supports\ApiPP;
use App\Models\PeristiwaRisiko;
use App\Models\SasaranProyek;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ApiPP::class, function () {
            return new ApiPP();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        
        // Trust the proxy
        if (config('app.force_https')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', true);
        }

        View::composer('navbars.navbar-vertical', function ($view) {
            $pendingSasaranCount = 0;
            $pendingPeristiwaCount = 0;
            $user = auth()->user();

            if (
                $user
                && Gate::allows('verification_mr')
                && in_array($user->level_id, [1, 2], true)
                && optional($user->unit)->unit_mr == 1
            ) {
                $pendingSasaranCount = SasaranProyek::where('status', 2)
                    ->where('approval_status', SasaranProyek::APPROVAL_PENDING)
                    ->count();

                if (Schema::hasColumn('peristiwa_risikos', 'approval_status')) {
                    $pendingPeristiwaCount = PeristiwaRisiko::where('status', PeristiwaRisiko::STATUS_CUSTOM)
                        ->where('approval_status', PeristiwaRisiko::APPROVAL_PENDING)
                        ->count();
                }
            }

            $view->with([
                'pendingSasaranCount' => $pendingSasaranCount,
                'pendingPeristiwaCount' => $pendingPeristiwaCount,
                'pendingVerifikasiCount' => $pendingSasaranCount + $pendingPeristiwaCount,
            ]);
        });
    }
}
