<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'member_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    

    public function canAccessPanel(Panel $panel): bool
    {
// removed from demo for brevity
//        if ($this->hasRole(['Super-Admin', 'admin'])) {
//            return true;
//        }

        if ($panel->getId() === 'admin') {
            return $this->isStaff();
        }

        if ($panel->getId() === 'voting') {
            // Call to Site:: model removed for simplicity in this demo app
            return $this->isMember(); //  && Site::votingIsOpen();
        }

        return false;
    }

    public function isMember(): bool
    {
        return filled(($this->member->id ?? null));
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isStaff(): bool
    {
        return false;
    }



}
