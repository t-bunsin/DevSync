<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import the trait

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;
      protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone_number',
    ];
   
}
