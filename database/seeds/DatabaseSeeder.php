<?php

use Illuminate\Database\Seeder;
use App\Models\Driver;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Driver::create([
            'firstName'=>'Amin',
            'lastName'=>'Ragheb'
        ]);
         $this->call(CarCategorySeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(UserSeeder::class);

    }
}
