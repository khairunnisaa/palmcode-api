<?php

namespace Database\Seeders;

use App\Models\IdVerification;
use App\Models\Member;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Country;

class IdVerificationSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            IdVerification::create([
                'member_id' => $i, // Assuming member_id is sequential
                'file_name' => 'id_verification_' . $i . '.jpg', // Example file name
                'link_url_path' => 'https://example.com/id_verification_' . $i . '.jpg', // Example link URL
            ]);
        }
    }
}
