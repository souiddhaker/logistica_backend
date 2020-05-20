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
         $this->call(CarCategorySeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(UserSeeder::class);

    }
}
