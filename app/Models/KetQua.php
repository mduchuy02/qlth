<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetQua extends Model
{
    use HasFactory;

    protected $table = 'ket_qua';
    protected $primaryKey = 'ma_kq';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'ma_sv',
        'ma_mh',
        'diem_qt',
        'diem_thi1',
        'diem_thi2',
        'diem_tb',
    ];

    public function sinhVien(): BelongsTo
    {
        return $this->belongsTo(SinhVien::class, 'ma_sv', 'ma_sv');
    }
    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(MonHoc::class, 'ma_mh', 'ma_mh');
    }
}
