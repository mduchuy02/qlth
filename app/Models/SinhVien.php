<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SinhVien extends Model
{
    use HasFactory;

    protected $table = 'sinh_vien';
    protected $primaryKey = 'ma_sv';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'ma_sv',
        'ten_sv',
        'ngay_sinh',
        'phai',
        'dia_chi',
        'sdt',
        'email',
        'ma_lop',
    ];

    public function lop(): BelongsTo
    {
        return $this->belongsTo(Lop::class, 'ma_lop', 'ma_lop');
    }

    public function users(): HasOne
    {
        return $this->hasOne(User::class, 'username', 'ma_sv');
    }
    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'ma_sv', 'ma_sv');
    }
}
