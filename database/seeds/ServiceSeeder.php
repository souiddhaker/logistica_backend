<?php

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Price;
use App\Models\Promocode;
use App\Models\CategoryServices;
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $service = CategoryServices::create(['title' => 'Service']);
        $packaging = CategoryServices::create(['title' => 'Packaging']);

        Service::create(['label' => 'Delivery', 'price' => 15 ,'category_id' => $service->id]);
        Service::create(['label' => 'Boarding', 'price' => 2 ,'category_id' => $service->id]);
        Service::create(['label' => 'Shipping', 'price' => 5 ,'category_id' => $service->id]);
        Service::create(['label' => 'Cartoon', 'price' => 5 ,'category_id' => $packaging->id]);
        Service::create(['label' => 'Plastic Roll', 'price' => 2.6 ,'category_id' => $packaging->id]);


        Price::create(['from'=> 1, 'to' => 5 , 'cost' => 50]);
        Price::create(['from'=> 5.1, 'to' => 10 , 'cost' => 100]);
        Price::create(['from'=> 10.1, 'to' => 20 , 'cost' => 150]);

        Promocode::create(['code' => '111111' , 'pourcentage' => 2 , 'status' => 'active', 'end_at'=>'2021-05-19 12:11:06']);
        Promocode::create(['code' => '222222' , 'pourcentage' => 5 , 'status' => 'active', 'end_at'=>'2021-05-19 12:11:06']);
        Promocode::create(['code' => '333333' , 'pourcentage' => 15 , 'status' => 'active', 'end_at'=>'2021-05-19 12:11:06']);
        Promocode::create(['code' => '444444' , 'pourcentage' => 15 , 'status' => 'inactive', 'end_at'=>'2021-05-19 12:11:06']);
    }
}
