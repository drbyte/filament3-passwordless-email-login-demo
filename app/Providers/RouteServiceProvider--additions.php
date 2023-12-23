<?php

namespace App\Providers;

use App\Http\Controllers\Auth\HandleMagicLinkController;
use Filament\Facades\Filament;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    // .. default Laravel skeleton content will be here
    
    public function boot(): void
    {
        // .. default Laravel skeleton content will be here
        
        
        // FILAMENT MAGIC-LINK: register the magic-link for each panel
        Route::name('filament.')
            ->group(function (){
                foreach (Filament::getPanels() as $panel) {
                    $panelId = $panel->getId();
                    $domains = $panel->getDomains();

                    foreach ((empty($domains) ? [null] : $domains) as $domain) {
                        Route::domain($domain)
                            ->middleware($panel->getMiddleware())
                            ->name("{$panelId}.")
                            ->prefix($panel->getPath())
                            ->group(function () {
                                Route::get('/login/magic-link/{key}/{remember?}', HandleMagicLinkController::class)
                                    ->middleware('signed')
                                    ->name('login.magic-link');
                            });
                    }
                }
            });

    }
}
