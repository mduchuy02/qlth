<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonHoc extends Model
{
    use HasFactory;
    protected $table = 'mon_hoc';
    protected $primaryKey = 'ma_mh';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ma_mh',
        'ten_mh',
        'so_tiet',
    ];


    public function lichDays(): HasMany
    {
        return $this->hasMany(LichDay::class, 'ma_mh', 'ma_mh');
    }
}
