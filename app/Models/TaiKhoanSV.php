<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaiKhoanSV extends Model
{
    use HasFactory;
    protected $table = 'tai_khoan_sv';
    protected $primaryKey = 'ma_sv';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; 

    protected $fillable = [
        'ma_sv',
        'mat_khau',
    ];

    public function sinhVien(): BelongsTo
    {
        return $this->belongsTo(SinhVien::class, 'ma_sv', 'ma_sv');
    }
}
