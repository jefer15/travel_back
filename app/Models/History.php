<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'search_history';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'city_id',
        'budget_cop',
        'exchange_rate_currency',
        'converted_amount',
        'weather',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
