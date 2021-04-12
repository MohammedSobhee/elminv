<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\MessagesType;

class MessagesTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = new MessagesType();
        $type->name = 'class';
        $type->save();

        $type = new MessagesType();
        $type->name = 'team';
        $type->save();

        $type = new MessagesType();
        $type->name = 'user';
        $type->save();

        $type = new MessagesType();
        $type->name = 'grade';
        $type->save();

        $type = new MessagesType();
        $type->name = 'global';
        $type->save();
    }
}
