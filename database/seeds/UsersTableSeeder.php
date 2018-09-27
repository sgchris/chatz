<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$greg1 = User::where('email', 'sgchris@gmail.com')->get()->first();
		if (!$greg1) {
			User::create([
				'name' => 'Gregory Chris',
				'email' => 'sgchris@gmail.com',
				'password' => bcrypt('123456'),
			]);
		}

		$greg2 = User::where('email', 'sgchris2@gmail.com')->get()->first();
		if (!$greg2) {
			User::create([
				'name' => 'Gregory Chris 2',
				'email' => 'sgchris2@gmail.com',
				'password' => bcrypt('123456'),
			]);
		}

		// random data
        $faker = Faker\Factory::create();
        for ($i=0; $i<10; ++$i) {
			User::create([
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => bcrypt('1234'),
            ]);
        } 
    }
}
