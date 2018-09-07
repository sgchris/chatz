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

        $chats = \App\Chat::all()->pluck('id');
        $users = \App\User::all()->pluck('id');

        $faker = Faker\Factory::create();
        for ($i=0; $i<50; ++$i) {
            $chatId = $chats[mt_rand(0, count($chats) - 1)];
            $users = \App\Chat::find($chatId)->users()->pluck('id');

            DB::table('messages')->insert([
                'message' => $faker->text,
                'chat_id' => $chatId,
                'user_id' => $users[mt_rand(0, count($users) - 1)],
                'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        } 
    }
}
