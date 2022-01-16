<?php

namespace Database\Seeders;

// use App\Models\User;
// use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\RolesTableSeeder;
// use Illuminate\Support\Str;
// use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
    }
}
