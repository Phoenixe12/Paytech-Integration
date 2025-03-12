<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type_event',
        'ref_command',
        'custom_field',
        'item_name',
        'item_price',
        'devise',
        'command_name',
        'env',
        'token',
        'api_key_sha256',
        'api_secret_sha256'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_field' => 'json',
        'item_price' => 'decimal:2',
    ];
}