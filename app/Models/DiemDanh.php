<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiemDanh extends Model
{
    use HasFactory;

    protected $table = 'diem_danh';
    protected $primaryKey = 'ma_dd';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'ma_sv',
        'ma_tkb',
        'ngay_hoc',
        'diem_danh1',
        'diem_danh2',
        'ghi_chu',
    ];

    public function tkb(): BelongsTo
    {
        return $this->belongsTo(Tkb::class, 'ma_tkb', 'ma_tkb');
    }
}
