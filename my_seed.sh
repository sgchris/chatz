#!/bin/bash


php artisan db:seed --class=UsersTableSeeder
php artisan db:seed --class=ChatsTableSeeder
php artisan db:seed --class=ChatUserTableSeeder
php artisan db:seed --class=MessagesTableSeeder
php artisan db:seed --class=RelationsTableSeeder
