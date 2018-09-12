<?php

use Illuminate\Database\Seeder;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Message::truncate();

        $users = \App\User::all()->pluck('id');
        $chats = \App\Chat::all()->pluck('id');

        $faker = Faker\Factory::create();
        for ($i=0; $i<50; ++$i) {
            $chatId = $chats[mt_rand(0, count($chats) - 1)];
            $chatUsers = \App\Chat::find($chatId)->users()->pluck('id');
			if (count($chatUsers) == 0) {
				continue;
			}

            DB::table('messages')->insert([
                'message' => $faker->text,
                'chat_id' => $chatId,
                'user_id' => $chatUsers[mt_rand(0, count($chatUsers) - 1)],
                'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        } 
    }
}
