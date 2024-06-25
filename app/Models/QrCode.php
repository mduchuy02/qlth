<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    use HasFactory;
    protected $table = 'qrcode';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'ma_tkb',
        'thoi_gian_kt',
    ];

    public function tkb(): BelongsTo
    {
        return $this->belongsTo(tkb::class, 'ma_tkb', 'ma_tkb');
    }
}
