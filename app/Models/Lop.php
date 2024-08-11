<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lop extends Model
{
    use HasFactory;

    protected $table = 'lop';
    protected $primaryKey = 'ma_lop';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ma_lop',
        'ten_lop',
        'ma_khoa',
        'gvcn',
        'sdt_gvcn',
    ];

    public function khoa(): BelongsTo
    {
        return $this->belongsTo(Khoa::class, 'ma_khoa', 'ma_khoa');
    }

    public function sinhViens(): HasMany
    {
        return $this->hasMany(SinhVien::class, 'ma_lop', 'ma_lop');
    }
}
