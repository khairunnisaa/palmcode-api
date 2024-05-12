<?php

namespace Database\Seeders;

use App\Models\IdVerification;
use App\Models\Member;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Country;

class BookingsSeeder extends Seeder
{
    public function run(): void
    {
        $memberIds = Member::pluck('id')->toArray();
        $countryIds = Country::pluck('id')->toArray();
        $idVerifications = IdVerification::pluck('id')->toArray();

        $numberOfBookings = 20;

        for ($i = 0; $i < $numberOfBookings; $i++) {
            $booking = new Booking();
            $booking->member_id = $memberIds[array_rand($memberIds)];
            $booking->country_id = $countryIds[array_rand($countryIds)];
            $booking->id_verification_id = $idVerifications[array_rand($idVerifications)];
            $booking->surfing_experience = rand(1, 10);
            $booking->visit_date = now()->addDays(rand(1, 30));
            $booking->desired_board = ['longboard', 'funboard', 'shortboard', 'fishboard', 'gunboard'][rand(0, 4)];
            $booking->save();
        }
    }
}
