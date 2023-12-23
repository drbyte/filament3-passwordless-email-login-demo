<?php

namespace App\Filament\EmailAuth;

use App\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class FilamentPasswordless
{
    protected string $model;
    protected array $related_models = [];

    public function __construct()
    {
        $this->model = \App\Models\User::class;
        $this->related_models = [
            \App\Models\Member::class,
            // other types removed from this demo
        ];
    }

    public function getModel(?string $email = null): ?Model
    {
        $email = $email ?? (Session::has('email') ? Session::get('email') : null);

        if (! $email) {
            return null;
        }

        $found = $this->model::query()->where('email', $email)->first();

        if ($found) {
            return $found;
        }

        // if not found in Users table, check Members and other tables
        foreach ($this->related_models as $model) {
            $foundNew = $model::query()->where('email', $email)->first();
            if ($foundNew) {
                // create a User record for the found model, and return the new record
                $search = [
                    'email' => $foundNew->email,
                ];
                $key = match($model) {
                    \App\Models\Member::class => 'member_id',
                    // other models removed from demo
                    default => null,
                };
                $values = [
                    'name' => $foundNew->name,
                    $key => $foundNew->id,
                    'password' => 'not used',
//                    'created_by' => 'Lookup', // this is a custom field on User model, only used for reference, not needed for anything critical
                ];

                // almost always this is gonna just be "create", cuz it won't actually be found; however using firstOrCreate for idempotency
                return $this->model::firstOrCreate($search, $values);
            }
        }

        // allow unknown persons to sign up during testing mode
        if (Site::testingMode()) {
            $attributes = [
                'name' => $email,
                'email' => $email,
                'password' => 'not used',
                'created_by' => 'Testing-Unknown',
            ];
            return $this->model::create($attributes);
        }

        return null;
    }

    /**
     * This should never return null unless the User no-longer-exists.
     * But we catch the Null later and throw a 401 in the controller.
     */
    public function getModelByRouteKey(string $key): ?Authenticatable
    {
        $routeKeyName = (new $this->model)->getRouteKeyName();

        return $this->model::query()
            ->where($routeKeyName, $key)
            ->first();
    }

    public function getUserById(int $id)
    {
        if (! $id) {
            return null;
        }

        return $this->model::query()->find($id);
    }
}
