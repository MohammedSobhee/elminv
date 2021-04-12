<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dev_permission = Permission::where('slug','create-tasks')->first();
        $manager_permission = Permission::where('slug', 'edit-users')->first();

        //RoleTableSeeder.php
        if(!Role::where('slug', 'developer')->exists()) {
            $dev_role = new Role();
            $dev_role->slug = 'developer';
            $dev_role->name = 'Front-end Developer';
            $dev_role->save();
            $dev_role->permissions()->attach($dev_permission);
        }

        if(!Role::where('slug', 'manager')->exists()) {
            $manager_role = new Role();
            $manager_role->slug = 'manager';
            $manager_role->name = 'Assistant Manager';
            $manager_role->save();
            $manager_role->permissions()->attach($manager_permission);
        }

        if(!Role::where('slug', 'teacher')->exists()) {
            $teacher_role = new Role();
            $teacher_role->slug = 'teacher';
            $teacher_role->name = 'Teacher';
            $teacher_role->save();
        }

        if(!Role::where('slug', 'student')->exists()) {
            $student_role = new Role();
            $student_role->slug = 'student';
            $student_role->name = 'Student';
            $student_role->save();
        }

        if(!Role::where('slug', 'admin')->exists()) {
            $admin_role = new Role();
            $admin_role->slug = 'admin';
            $admin_role->name = 'Admin';
            $admin_role->save();
            $admin_role->permissions()->attach($manager_permission);
        }

        if(!Role::where('slug', 'school-admin')->exists()) {
            $school_admin_role = new Role();
            $school_admin_role->slug = 'school-admin';
            $school_admin_role->name = 'School Admin';
            $school_admin_role->save();
        }

        if(!Role::where('slug', 'assistant-teacher')->exists()) {
            $asst_teacher_role = new Role();
            $asst_teacher_role->slug = 'assistant-teacher';
            $asst_teacher_role->name = 'Assistant Teacher';
            $asst_teacher_role->save();
        }

        if(!Role::where('slug', 'superintendent')->exists()) {
            $super_int_role = new Role();
            $super_int_role->slug = 'superintendent';
            $super_int_role->name = 'Superintendent';
            $super_int_role->save();
        }

    }
}
