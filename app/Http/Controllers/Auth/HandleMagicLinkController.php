<?php

namespace App\Http\Controllers\Auth;

use App\Filament\EmailAuth\FilamentPasswordless;
use App\Http\Controllers\Controller as BaseController;
use App\Models\Site;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Livewire\Features\SupportRedirects\Redirector;

class HandleMagicLinkController extends RoutingController
{
    public function __invoke(Request $request, string $key, bool $remember = false): RedirectResponse | Redirector
    {
        // determine which user model to log in as
        $model = app(FilamentPasswordless::class)->getModelByRouteKey($key);

        // if we don't have a valid model, then either the link was heavily tampered with or the User record was deleted
        abort_if(empty($model), 401, 'Invalid login.');

        // this is probably already handled by core ValidateSignature middleware, but kept here for thoroughness:
        abort_unless($request->hasValidSignature(), 401, 'Invalid login link. Please request a new one.');

        // log the user in
        Filament::auth()->login($model, $remember);
        session()->regenerate();

        // send them to the right panel based on Site status
        $destination = $this->getPanelPathFromSiteStatus();

        // filter for member type
        if ($model->isMember()) {
            // members shouldn't be trying to access any other area, regardless which panel they tried to login from
            $destination = '/vote';
        }

        // (removed other member-type lookups from demo, for brevity. They just set other $destination based on various logic)

        return redirect()->intended($destination);
    }

    // This is just a way to determine $destination based on status. This uses an Enum lookup, but anything could be used.
    protected function getPanelPathFromSiteStatus(): string
    {
    
        // for sake of demo, just returning '/' here:
        return '/';
    
//        $enumStatus = Site::currentStatus();
//        if (!$enumStatus) {
//            return '/';
//        }
//
//        return $enumStatus->getPanelPath();
    }
}
