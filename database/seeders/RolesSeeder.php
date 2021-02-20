<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'admin',
            'pages-editor',
            'blogs-editor',
            'pages-publisher',
            'blogs-publisher',
            'livestreams-manager',
            'blogs-manager',
            'inquiries-manager',
        ];

        foreach ($roles as $name) {
            if (!Role::where('name', $name)->first()) {
                $role = new Role;
                $role->name = $name;
                $role->save();
            }
        }
    }
}
