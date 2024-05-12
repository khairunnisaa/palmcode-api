<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use App\Models\User;

class MembersTableSeeder extends Seeder
{
    public function run()
    {
        // Existing users
        Member::create([
            'name' => 'John Doe',
            'whatsapp_number' => '1234567890',
        ]);

        Member::create([
            'name' => 'Jane Smith',
            'whatsapp_number' => '9876543210',
        ]);

        Member::create([
            'name' => 'Alice Johnson',
            'whatsapp_number' => '5555555555',
        ]);
    }
}
