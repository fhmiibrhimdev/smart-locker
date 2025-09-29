<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin User
        $adminUser = User::create([
            'name' => 'Fahmi Ibrahim',
            'email' => 'fahmi@admin.com',
            'password' => Hash::make('1'),
            'active' => '1',
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminUser->addRole($adminRole);
        }

        $this->command->info('Admin user created: ' . $adminUser->email);

        // Create Kurir User
        $kurirUser = User::create([
            'name' => 'Latif',
            'email' => 'latif@kurir.com',
            'password' => Hash::make('1'),
            'active' => '1',
            'email_verified_at' => now(),
        ]);

        // Assign kurir role
        $kurirRole = Role::where('name', 'kurir')->first();
        if ($kurirRole) {
            $kurirUser->addRole($kurirRole);
        }

        $this->command->info('Kurir user created: ' . $kurirUser->email);

        // Create Regular User
        $regularUser = User::create([
            'name' => 'Dzaki',
            'email' => 'dzaki@user.com',
            'password' => Hash::make('1'),
            'active' => '1',
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $regularUser->addRole($userRole);
        }

        $this->command->info('Regular user created: ' . $regularUser->email);
    }
}
