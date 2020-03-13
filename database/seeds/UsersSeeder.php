<?php

use Illuminate\Database\Seeder;

use App\User;

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
        //$mike->password = bcrypt(Str::random(8));
        $mike->password = bcrypt('q');
        $mike->email_verified_at = now();
        $mike->save();

        $mike->addRole('admin');

        $brent = new User;
        $brent->name = 'Brent lee';
        $brent->email = 'brent.lee@brentwood.bc.ca';
        //$brent->password = bcrypt(Str::random(8));
        $brent->password = bcrypt('q');
        $brent->email_verified_at = now();
        $brent->save();

        $brent->addRole('admin');
    }
}
