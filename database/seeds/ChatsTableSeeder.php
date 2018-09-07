<?php

use Illuminate\Database\Seeder;

class ChatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i=0; $i<10; ++$i) {
            DB::table('chats')->insert([
                'name' => $faker->company,
            ]);
        } 
    }

}
