<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user = User::create([
        //     'name' => 'Yeremia Alfa Dio',
        //     'email' => 'raikkonendio@gmail.com',
        //     'password' => bcrypt('password'),
        //     'email_verified_at' => Carbon::now(),
        //     'remember_token' => Str::Random(50),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        // $user->assignRole('admin');
        // $admin = DB::table('users')->insert([
        //     'name' => 'dio',
        //     'email' => 'raikkonendio@gmail.com',
        //     'email_verified_at' => Carbon::now(),
        //     'remember_token' => Str::Random(50),
        //     'password' => Hash::make('password'),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now(),
        // ]);
        // DB::table('users')->insert([
        //     'name' => 'teacher',
        //     'email' => 'teacher@xyz.com',
        //     'email_verified_at' => Carbon::now(),
        //     'remember_token' => Str::Random(50),
        //     'password' => Hash::make('password'),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now()
        // ]);
        // DB::table('users')->insert([
        //     'name' => 'student',
        //     'email' => 'student@xyz.com',
        //     'email_verified_at' => Carbon::now(),
        //     'remember_token' => Str::Random(50),
        //     'password' => Hash::make('password'),
        //     'created_at' => Carbon::now(),
        //     'updated_at' => Carbon::now()
        // ]);
    }
}
