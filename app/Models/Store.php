<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Store extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name_store',
        'owner_name',
        'address',
        'phone',
        'email',
        'password',
        'logo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getLogoUrlAttribute()
    {
        return asset('storage/' . $this->logo);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'store';
    }

    // public function getUserName(): string
    // {
    //     return $this->name_store ?? $this->owner_name ?? $this->email ?? 'Store User';
    // }

    public function getNameAttribute(): string
    {
        return $this->name_store ?? $this->owner_name ?? $this->email ?? 'Store User';
    }

    public function product() {
        return $this->hasMany(Product::class);
    }
}
