<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mike = new User;
        $mike->name = 'Mike Minckler';
        $mike->email = 'mike.minckler@brentwood.ca';
        $mike->password = bcrypt(Str::random(8));
        $mike->email_verified_at = now();
        $mike->save();

        $mike->addRole('admin');

        $brent = new User;
        $brent->name = 'Brent Lee';
        $brent->email = 'brent.lee@brentwood.ca';
        $brent->password = bcrypt(Str::random(8));
        $brent->email_verified_at = now();
        $brent->save();

        $brent->addRole('admin');
    }
}
