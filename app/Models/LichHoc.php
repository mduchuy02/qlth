<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichHoc extends Model
{
    use HasFactory;
    
    protected $table = 'lich_hoc';
    protected $primaryKey = [
        'ma_sv', 
        'ma_gd'
    ];

    protected $keyType = 'mixed';
    public $timestamps = false;

    protected $fillable = [
        'ma_sv',
        'ma_gd',
    ];

    public function sinhVien(): BelongsTo
    {
        return $this->belongsTo(SinhVien::class, 'ma_sv', 'ma_sv');
    }
    public function lichGD(): BelongsTo
    {
        return $this->belongsTo(LichDay::class, 'ma_gd', 'ma_gd');
    }
}