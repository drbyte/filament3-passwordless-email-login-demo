<?php

namespace App\Filament\EmailAuth;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

/** @phpstan-consistent-constructor */
class MagicLink
{
    protected int $expiry = 15;
    protected string $signedUrl;

    public static function create(Model $model, bool $remember = false): static
    {
        return new static($model, $remember);
    }

    public function __construct(
        protected Model $model,
        protected bool $remember = false
    )
    {
        $this->generateUrl();
    }

    public function getExpiry(): int
    {
        return $this->expiry;
    }

    public function getSignedUrl(): string
    {
        return $this->signedUrl;
    }

    protected function generateUrl(): void
    {
        $panelId = 'auth';

        $panel = Filament::getCurrentPanel();
        if ($panel) {
            $panelId = $panel->getId();
        }

        $signedUrl = URL::temporarySignedRoute(
            name: "filament.{$panelId}.login.magic-link",
            expiration: now()->addMinutes($this->getExpiry()),
            parameters: [
                'key' => $this->model->getRouteKey(),
                'remember' => $this->remember,
            ]
        );

        $this->signedUrl = $signedUrl;
    }
}
