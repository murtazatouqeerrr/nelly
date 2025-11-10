<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            FloridaRolesSeeder::class,
            CourseSeeder::class,
            FloridaBDICourseSeeder::class,
            MissouriMasterSeeder::class,
            PrivacyPolicySeeder::class,
            StateSeeder::class,
            EmailTemplateSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
