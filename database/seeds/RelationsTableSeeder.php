<?php

use Illuminate\Database\Seeder;

class RelationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('relations')->truncate();
        
		$users = \App\User::all()->pluck('id');
        $pairs = [];
        for ($i=0; $i<30; ++$i) {

			// select 2 users
            $userId = $users[mt_rand(0, count($users) - 1)];
            $user2Id = $users[mt_rand(0, count($users) - 1)];
			if ($userId == $user2Id) continue;

            $pairName = $userId.'_'.$user2Id;
            if (!array_key_exists($pairName, $pairs)) {
                $pairs[$pairName] = true;
                
                DB::table('relations')->insert([
                    'user_id' => $userId,
                    'friend_id' => $user2Id,
                ]);
            }
        } 
    }
}
