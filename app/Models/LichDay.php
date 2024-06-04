<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LichDay extends Model
{
    use HasFactory;

    protected $table = 'lich_gd';
    protected $primaryKey = 'ma_gd';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'ma_gv',
        'ma_mh',
        'phong_hoc',
        'thoi_gian',
        'st_bd',
        'st_kt',
    ];

    public function giaoVien(): BelongsTo
    {
        return $this->belongsTo(GiaoVien::class, 'ma_gv','ma_gv');
    }

    public function lichHocs(): HasMany
    {
        return $this->hasMany(LichHoc::class, 'ma_gd', 'ma_gd');
    }

    public function tkbs(): HasMany
    {
        return $this->hasMany(Tkb::class, 'ma_gd', 'ma_gd');
    }

    public function monHoc(): BelongsTo
    {
        return $this->belongsTo(MonHoc::class, 'ma_mh', 'ma_mh');
    }
}
