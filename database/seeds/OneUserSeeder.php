<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\Address;
use App\Models\Service;
use App\Models\SubService;

class OneUserSeeder extends Seeder
{

    public function createTrip(int $userID,int $driverID,string $status)
    {
        $trip1 = Trip::create([
            'status'=>$status,
            'total_price'=>20,
            'nbr_luggage'=>2,
            'type_car_id'=>1,
            'driver_note'=>'driver note',
            'user_id'=>$userID,
            'pickup_at'=>'2020-06-12 08:00:00',
            'driver_id'=>$driverID]);
        $pickUp = Address::create([
            'primaryName'=>'King Abdulaziz International Airport',
            'secondaryName'=>'Airport In Riyadh, Saudi Arabia',
            'type'=>'1',
            'longitude'=>round(0,1000),
            'lattitude'=>round(0,1000),
            'place_id'=>(string) rand(0,100),
            'user_id'=>$userID]);
        $destination = Address::create([
            'primaryName'=>'King Fahd International Airport',
            'secondaryName'=>'Dammam Arabie saoudite',
            'longitude'=>round(0,1000),
            'lattitude'=>round(0,1000),
            'type'=>'2',
            'place_id'=>(string) rand(0,100),
            'user_id'=>$userID]);
        $trip1->addresses()->attach($pickUp);
        $trip1->addresses()->attach($destination);
        $ar = [1, 3, 4];
        foreach ($ar as &$value) {
            $service = Service::find($value);

            if ($service)
            {
                $trip1->services()->attach($service);
            }
        }

        $subService = SubService::find(1);
        $trip1->subservices()->attach($subService);

    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(5);
        $driver = User::find(2);
        foreach (range(1, 15) as $i) {
            $this->createTrip($user->id,$driver->id,"1");
            $this->createTrip($user->id,$driver->id,"2");
            $this->createTrip($user->id,$driver->id,"3");
        }
    }
}
