<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Admin extends Model
{
    use HasApiTokens, HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'admin_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'email',
        'full_name',
        'role'
    ];
}

