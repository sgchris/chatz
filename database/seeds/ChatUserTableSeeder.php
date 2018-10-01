<?php

use Illuminate\Database\Seeder;

class ChatUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('chat_user')->truncate();
        
		$users = \App\User::all()->pluck('id');
        $chats = \App\Chat::all()->pluck('id');
        $pairs = [];
        for ($i=0; $i<30; ++$i) {
            $userId = $users[mt_rand(0, count($users) - 1)];
            $user2Id = $users[mt_rand(0, count($users) - 1)];
            $chatId = $chats[mt_rand(0, count($chats) - 1)];
            $pairName = $userId.'_'.$chatId;
            if (!array_key_exists($pairName, $pairs)) {
                $pairs[$pairName] = true;
                
                DB::table('chat_user')->insert([
                    'user_id' => $userId,
                    'chat_id' => $chatId,
                ]);
            }
        } 
    }
}
