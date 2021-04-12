<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Role;
use App\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dev_role = Role::where('slug','developer')->first();
        $manager_role = Role::where('slug', 'manager')->first();
        $dev_perm = Permission::where('slug','create-tasks')->first();
        $manager_perm = Permission::where('slug','edit-users')->first();

        // $developer = new User();
        // $developer->name = 'james speicher';
        // $developer->email = 'speicherjames@yahoo.com';
        // $developer->password = bcrypt('invent');
        // $developer->save();
        // $developer->roles()->attach($dev_role);
        // $developer->permissions()->attach($dev_perm);

        // $manager = new User();
        // $manager->name = 'Ahmed Arafat';
        // $manager->email = 'arafat.ahmed@davison.com';
        // $manager->password = bcrypt('invent');
        // $manager->save();
        // $manager->roles()->attach($manager_role);
        // $manager->permissions()->attach($manager_perm);

        // $manager = new User();
        // $manager->name = 'Kristi Russell';
        // $manager->email = 'krussell@flyingcork.com';
        // $manager->password = bcrypt('invent');
        // $manager->save();
        // $manager->roles()->attach($manager_role);
        // $manager->permissions()->attach($manager_perm);

        // $manager = new User();
        // $manager->name = 'Nathan Field';
        // $manager->email = 'field.nathan@inventionlandinstitute.com';
        // $manager->password = bcrypt('invent');
        // $manager->save();
        // $manager->roles()->attach($manager_role);
        // $manager->permissions()->attach($manager_perm);

        // $manager = new User();
        // $manager->name = 'Jenna Campbell';
        // $manager->email = 'campbell.jenna@inventionland.com';
        // $manager->password = bcrypt('invent');
        // $manager->save();
        // $manager->roles()->attach($manager_role);
        // $manager->permissions()->attach($manager_perm);

        $manager = new User();
        $manager->name = 'Tester Test';
        $manager->first_name = 'Tester';
        $manager->last_name = 'Test';
        $manager->school_id = '30';
        $manager->email = 'krussell@flyingcork.com';
        $manager->password = bcrypt('invent');
        $manager->save();
        $manager->roles()->attach($manager_role);
        $manager->permissions()->attach($manager_perm);
    }
}
