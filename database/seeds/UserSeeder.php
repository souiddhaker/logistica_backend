<?php

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\Trip;
use App\Models\User;
use App\Models\Driver;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Notif;
class UserSeeder extends Seeder
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
        $this->createUser();
        //
        $driver = User::where('roles','=',json_encode(['captain']))->first();

        $listUser = User::where('roles','=', json_encode(['client']))->get();
        foreach ($listUser as $user)
        {

            $userAddress = Address::where('user_id',$user->id)->get();
            if (count($userAddress) == 0)
            {
                foreach (range(1, 15) as $i) {
                    $this->createTrip($user->id,$driver->id,"1");
                    $this->createTrip($user->id,$driver->id,"2");
                    $this->createTrip($user->id,$driver->id,"3");
                }
                Address::create([
                    'primaryName'=>'King Abdulaziz International Airport',
                    'secondaryName'=>'Airport In Riyadh, Saudi Arabia',
                    'longitude'=>'39.156899',
                    'type'=>'3',
                    'lattitude'=>'21.706231','user_id'=>$user->id]);
                Address::create([
                    'primaryName'=>'King Fahd International Airport',
                    'secondaryName'=>'Dammam Arabie saoudite',
                    'longitude'=>'49.797523',
                    'lattitude'=>'26.482629',
                    'type'=>'3',
                    'user_id'=>$user->id]);
                Address::create([
                    'primaryName'=>'Medina Airport',
                    'secondaryName'=>'Medina Arabie saoudite',
                    'longitude'=>'24.557606',
                    'type'=>'3',
                    'lattitude'=>'24.557606','user_id'=>$user->id]);

                Notif::create([
                    'title'=>'Your booking #1234 has been succesfull',
                    'type'=>'System',
                    'icon'=>'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description'=>'Description booking',
                    'user_id'=>$user->id]);

                Notif::create([
                    'title'=>'Your booking #1010 has been cancelled',
                    'type'=>'System',
                    'icon'=>'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description'=>'Description booking',
                    'user_id'=>$user->id]);

                Notif::create([
                    'title'=>'Invite friends - Get 3 coupons each!',
                    'type'=>'Promotion',
                    'icon'=>'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description'=>'Invite friends - Get 3 coupons each!',
                    'user_id'=>$user->id]);
            }
        }

    }
    public function createUser(){
        if(User::where('email','client@mail.com')->count('id')===0){
            User::create([
                'firstName' => 'client firstName',
                'lastName' => 'lastName',
                'email' => 'client@mail.com',
                'phone' => '123456321',
                'password' => bcrypt('logistica'),
                'roles'=>json_encode(['client'])
            ]);
            User::create([
                'firstName' => 'captain firstName',
                'lastName' => 'lastName',
                'email' => 'captain@mail.com',
                'phone' => '123456258',
                'password' => bcrypt('logistica'),
                'roles'=>json_encode(['captain'])
            ]);
            User::create([
                'firstName' => 'admin firstName',
                'lastName' => 'lastName',
                'email' => 'admin@mail.com',
                'phone' => '123456258',
                'password' => bcrypt('logistica'),
                'roles'=>json_encode(['admin'])
            ]);
        }
    }
}
