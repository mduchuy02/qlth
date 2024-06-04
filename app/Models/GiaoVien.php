<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GiaoVien extends Model
{
    use HasFactory;

    protected $table = 'giao_vien';
    protected $primaryKey = 'ma_gv';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ma_gv',
        'ten_gv',
        'ngay_sinh',
        'phai',
        'dia_chi',
        'sdt',
        'email',
    ];

    public function taiKhoanGV(): HasOne
    {
        return $this->hasOne(TaiKhoanGV::class, 'ma_gv', 'ma_gv');
    }

    
    public function lichDays(): HasMany
    {
        return $this->hasMany(LichDay::class, 'ma_gv', 'ma_gv');
    }
}
