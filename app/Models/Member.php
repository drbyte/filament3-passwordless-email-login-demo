<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * The Member model is just a list of "authorized" members allowed to access the system. 
 * It is used as a lookup datasource, and does not directly handle Auth or Login: those are all done by the User model.
 */ 
class Member extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];


    public function canAccessPanel(Panel $panel): bool
    {
        // This model should never be used for login, so we always return false here.
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'member_id');
    }

}
