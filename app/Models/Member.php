<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema()
 */
class Member extends Model
{
    protected $fillable = ['name', 'email', 'whatsapp_number'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function idVerifications(): HasMany
    {
        return $this->hasMany(IdVerification::class);
    }
}

