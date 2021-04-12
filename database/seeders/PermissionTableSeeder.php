<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use App\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       //PermissionTableSeeder.php
        $teacher_role = Role::where('slug', 'teacher')->first();


        $coursenotes = new Permission();
        $coursenotes->slug = 'course-notes';
        $coursenotes->name = 'Course Notes';
        $coursenotes->save();
        $coursenotes->roles()->attach($teacher_role);

        $grades = new Permission();
        $grades->slug = 'grades';
        $grades->name = 'Grades';
        $grades->save();
        $grades->roles()->attach($teacher_role);

        $studentadmin = new Permission();
        $studentadmin->slug = 'student-admin';
        $studentadmin->name = 'Student Admin';
        $studentadmin->save();
        $studentadmin->roles()->attach($teacher_role);




    }
}
