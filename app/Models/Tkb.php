<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tkb extends Model
{
    use HasFactory;

    protected $table = 'tkb';
    protected $primaryKey = 'ma_tkb';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'ma_gd',
        'ngay_hoc',
        'phong_hoc',
        'st_bd',
        'st_kt',
    ];

    public function diemDanh(): HasOne
    {
        return $this->hasOne(DiemDanh::class, 'ma_tkb', 'ma_tkb');
    }

    public function lichDay(): BelongsTo
    {
        return $this->belongsTo(LichDay::class, 'ma_gd', 'ma_gd');
    }
    public function qrcode(): HasMany
    {
        return $this->hasMany(QrCode::class, 'ma_tkb', 'ma_tkb');
    }
}
