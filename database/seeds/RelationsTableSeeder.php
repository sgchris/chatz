<?php

use Illuminate\Database\Seeder;

use App\User;

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
        
		$users = User::all()->pluck('id');
        $pairs = [];
        for ($i=0; $i<30; ++$i) {

			// select 2 users
            $userId = $users[mt_rand(1, count($users) - 1)];
            $user2Id = $users[mt_rand(1, count($users) - 1)];
			if ($userId == $user2Id) continue;

            $pairName = $userId.'_'.$user2Id;
            if (!array_key_exists($pairName, $pairs)) {
                $pairs[$pairName] = true;

				// set the opposite too
				$pairName2= $user2Id.'_'.$userId;
                $pairs[$pairName2] = true;
                
                DB::table('relations')->insert([
                    'user_id' => $userId,
                    'friend_id' => $user2Id,
					'updated_at' => \Carbon\Carbon::now(),
					'created_at' => \Carbon\Carbon::now(),
                ]);
            }
        } 


		// generate for my user
		$this->addForMyUser();
    }

	// generate friends for 'sgchris' user (my test user);
	protected function addForMyUser()
	{
		$userIds = User::where('id', '!=', 1)->get()->pluck('id')->all();
		$usersUsed = [];

		// add friends
		for ($i=0; $i<4; ++$i) {
			$userId = $userIds[mt_rand(0, count($userIds) - 1)];
			if (!in_array($userId, $usersUsed)) { 
				$usersUsed[] = $userId;

				echo "adding friend ".(User::find($userId)->name)." ({$userId})\n";
				DB::table('relations')->insert([
					'user_id' => 1,
					'friend_id' => $userId,
					'updated_at' => \Carbon\Carbon::now(),
					'created_at' => \Carbon\Carbon::now(),
				]);
			}
		}

		// add followers
		for ($i=0; $i<4; ++$i) {
			$userId = $userIds[mt_rand(0, count($userIds) - 1)];
			if (!in_array($userId, $usersUsed)) { 
				$usersUsed[] = $userId;

				echo "adding follower ".(User::find($userId)->name)." ({$userId})\n";
				DB::table('relations')->insert([
					'user_id' => $userId,
					'friend_id' => 1,
					'updated_at' => \Carbon\Carbon::now(),
					'created_at' => \Carbon\Carbon::now(),
				]);
			}
		}
	}
}
