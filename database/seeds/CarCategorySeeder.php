<?php

use Illuminate\Database\Seeder;
use App\Models\CarCategory;

class CarCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        CarCategory::create([
            'model' => 'Logística Car',
            'price' => 12,
            'capacity' => 3,
            'image' => 'https://logistica.wi-mobi.com/img/icon/car.png',
            'range_luggage' => '1-3',
        ]);
        CarCategory::create([
            'model' => 'Logística Van',
            'price' => 15,
            'capacity' => 5,
            'image' => 'https://logistica.wi-mobi.com/img/icon/van.png',
            'range_luggage' => '5',
        ]);

    }
}
