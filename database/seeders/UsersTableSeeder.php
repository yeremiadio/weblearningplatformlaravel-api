<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Main User
        $user_one = User::create([
            'name' => 'yeremiadio',
            'email' => 'raikkonendio@gmail.com',
            'password' => bcrypt('Babylon678`'),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user_one->assignRole('admin');
        $user_two = User::create([
            'name' => 'yeremiadio2',
            'email' => 'raikkonendio1@gmail.com',
            'password' => bcrypt('Babylon678`'),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user_two->assignRole('teacher');

        $user_three = User::create([
            'name' => 'yeremiadio2',
            'email' => 'yeremia.18002@mhs.unesa.ac.id',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
            'remember_token' => Str::Random(50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $user_three->assignRole('student');
    }
}
