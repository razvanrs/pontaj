<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GenerateUserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $collection = collect([
            'Role',
            'Permission',
            'BusinessUnit',
            'BusinessUnitEmployee',
            'BusinessUnitUser',
            'Day',
            'DayLimit',
            'Employee',
            'EmployeeSchedule',
            'EmployeeScheduleReconcile',
            'LearningActivityType',
            'Location',
            'Module',
            'Month',
            'ScheduleStatus',
            'Teacher',
            'TeacherSchedule',
            'Theme',
            'User',
            'Week',
            'Year',
            'StudentSanction',
            'MilitaryRankType',
            'Sanction',
            'Student',
            'StudentClass',

        ]);

        $collection->each(function ($item, $key) {
            // create permissions for each collection item
            Permission::create(['group' => $item, 'name' => 'viewAny' . $item]);
            Permission::create(['group' => $item, 'name' => 'view' . $item]);
            Permission::create(['group' => $item, 'name' => 'create' . $item]);
            Permission::create(['group' => $item, 'name' => 'update' . $item]);
            Permission::create(['group' => $item, 'name' => 'delete' . $item]);
            Permission::create(['group' => $item, 'name' => 'restore' . $item]);
            Permission::create(['group' => $item, 'name' => 'forceDelete' . $item]);
        });


        // Create a Super-Admin Role and assign all Permissions
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        //Give User Super-Admin Role
        $superAdmins = [
            'marius.cirstea@magicpixel.ro',
            'claudiu.plesa@magicpixel.ro',
            'razzvan19@yahoo.com',
        ];
        $user = User::whereIn('email', $superAdmins)->get()->each(function ($user) {
            $user->assignRole('super-admin');
        });
    }
}
