<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class TaiKhoanGV extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'tai_khoan_gv';
    protected $primaryKey = 'ma_gv';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; 

    protected $fillable = [
        'ma_gv',
        'mat_khau',
    ];

    public function giaoVien(): BelongsTo
    {
        return $this->belongsTo(GiaoVien::class, 'ma_gv', 'ma_gv');
    }
}
