<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Database\Seeders\RolesSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\PagesSeeder;
use Database\Seeders\TagsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(PagesSeeder::class);
        $this->call(TagsSeeder::class);
    }
}
