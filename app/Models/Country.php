<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema()
 */
class Country extends Model
{
    protected $fillable = ['name', 'flag_url', 'code'];
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
