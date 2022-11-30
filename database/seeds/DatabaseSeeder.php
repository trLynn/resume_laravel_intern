<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(AdminLevelSeeder::class);
        $this->call(EmployeeAdminLevelsSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(LayoutSeeder::class);
        $this->call(LevelCategoriesSeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(AdminLevelSeeder::class);
        $this->call(EmployeeAdminLevelsSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(ConstantCollectionSeeder::class);
        $this->call(LevelSeeder::class);
    }
}
