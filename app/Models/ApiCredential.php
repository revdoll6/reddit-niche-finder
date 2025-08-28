<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'client_id',
        'client_secret',
        'username',
        'user_agent',
        'is_connected',
        'last_connected_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_connected' => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Get the user that owns the API credentials.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Encrypt the client secret when setting it.
     *
     * @param string $value
     * @return void
     */
    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt the client secret when getting it.
     *
     * @param string $value
     * @return string|null
     */
    public function getClientSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
} 