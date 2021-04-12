<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ClassType;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type = new ClassType();
        $type->name = 'K-3 Grades';
        $type->alt_name = 'Elementary';
        $type->slug = 'elementary';
        $type->save();

        $type = new ClassType();
        $type->name = '4-5 Grades';
        $type->alt_name = 'Elementary';
        $type->slug = 'elementary';
        $type->save();

        $type = new ClassType();
        $type->name = '6-8 Grades';
        $type->alt_name = 'Middle School';
        $type->slug = 'middle-school';
        $type->save();

        $type = new ClassType();
        $type->name = '9-12+ Grades';
        $type->alt_name = 'High School';
        $type->slug = 'high-school';
        $type->save();
    }
}
