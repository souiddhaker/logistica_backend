<?php

use App\Models\AdminRoles;
use App\Models\CancelTrip;
use App\Models\Result;
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

    public function createTrip(int $userID, int $driverID, string $status)
    {
        $trip1 = Trip::create([
            'status' => $status,
            'total_price' => 20,
            'nbr_luggage' => 2,
            'type_car_id' => 1,
            'driver_note' => 'driver note',
            'user_id' => $userID,
            'pickup_at' => '2020-06-12 08:00:00',
            'driver_id' => $driverID]);
        $pickUp = Address::create([
            'primaryName' => 'King Abdulaziz International Airport',
            'secondaryName' => 'Airport In Riyadh, Saudi Arabia',
            'type' => '1',
            'longitude' => round(0, 1000),
            'lattitude' => round(0, 1000),
            'place_id' => (string)rand(0, 100),
            'user_id' => $userID]);
        $destination = Address::create([
            'primaryName' => 'King Fahd International Airport',
            'secondaryName' => 'Dammam Arabie saoudite',
            'longitude' => round(0, 1000),
            'lattitude' => round(0, 1000),
            'type' => '2',
            'place_id' => (string)rand(0, 100),
            'user_id' => $userID]);
        $trip1->addresses()->attach($pickUp);
        $trip1->addresses()->attach($destination);
        $ar = [1, 3, 4];
        foreach ($ar as &$value) {
            $service = Service::find($value);

            if ($service) {
                $trip1->services()->attach($service);
            }
        }

        $subService = SubService::find(1);
        $trip1->subservices()->attach($subService);
        return $trip1;
    }

    public function cancelTrip($data)
    {


        $trip = Trip::find($data['trip_id']);

        if ($trip) {
            $cancelTrip = new CancelTrip();
            $cancelTrip->raison = $data['raison'];
            $cancelTrip->by_user = $data['canceledByUser'];
            $trip->status = '3';
            $trip->cancelTrip()->save($cancelTrip);
            $trip->save();

        }

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
        $driver = User::where('roles', '=', json_encode(['captain']))->first();

        $listUser = User::where('roles', '=', json_encode(['client']))->get();
        foreach ($listUser as $user) {

            $userAddress = Address::where('user_id', $user->id)->get();
            if (count($userAddress) == 0) {
                foreach (range(1, 15) as $i) {
                    $this->createTrip($user->id, $driver->id, "1");
                    $this->createTrip($user->id, $driver->id, "2");
                    $tripDetails = $this->createTrip($user->id, $driver->id, "2");
                    $this->cancelTrip(['raison' => 'his didnt reach me in time', 'canceledByUser' => true, 'trip_id' => $tripDetails['id']]);

                }
                Address::create([
                    'primaryName' => 'King Abdulaziz International Airport',
                    'secondaryName' => 'Airport In Riyadh, Saudi Arabia',
                    'longitude' => '39.156899',
                    'type' => '3',
                    'lattitude' => '21.706231', 'user_id' => $user->id]);
                Address::create([
                    'primaryName' => 'King Fahd International Airport',
                    'secondaryName' => 'Dammam Arabie saoudite',
                    'longitude' => '49.797523',
                    'lattitude' => '26.482629',
                    'type' => '3',
                    'user_id' => $user->id]);
                Address::create([
                    'primaryName' => 'Medina Airport',
                    'secondaryName' => 'Medina Arabie saoudite',
                    'longitude' => '24.557606',
                    'type' => '3',
                    'lattitude' => '24.557606', 'user_id' => $user->id]);

                Notif::create([
                    'title' => 'Your booking #1234 has been succesfull',
                    'type' => 'System',
                    'icon' => 'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description' => 'Description booking',
                    'user_id' => $user->id]);

                Notif::create([
                    'title' => 'Your booking #1010 has been cancelled',
                    'type' => 'System',
                    'icon' => 'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description' => 'Description booking',
                    'user_id' => $user->id]);

                Notif::create([
                    'title' => 'Invite friends - Get 3 coupons each!',
                    'type' => 'Promotion',
                    'icon' => 'https://logistica.wi-mobi.com/img/icon/icon.png',
                    'description' => 'Invite friends - Get 3 coupons each!',
                    'user_id' => $user->id]);
            }
        }

    }

    public function createUser()
    {
        if (User::where('email', 'client@mail.com')->count('id') === 0) {
            $client = User::create([
                'firstName' => 'client firstName',
                'lastName' => 'lastName',
                'email' => 'client@mail.com',
                'phone' => '123456321',
                'password' => bcrypt('logistica'),
                'roles' => json_encode(['client'])
            ]);
            $user = User::create([
                'firstName' => 'captain firstName',
                'lastName' => 'lastName',
                'email' => 'captain@mail.com',
                'phone' => '123456258',
                'password' => bcrypt('logistica'),
                'roles' => json_encode(['captain'])
            ]);
            $user->fcmUser()->create([
                'token' => 'f_lxoOcE-AI:APA91bGAI74QC0z3_LamCa-8dEyey27KdwoYCC0Xue8HcvstEkYpDzo4BvIdJ8Otno1qlymsjsSSH7XBD9-viDgBy-kEozIHk5_kBgOAZ8mfZKGkQfG107-ILz9upgkxmUK2ugPxoyAh'
            ]);

            $user->profileDriver()->create(['status' => 0, 'cartype_id' => 1, 'is_active' => 0]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'4',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1592904799.jpeg",
            ]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'4',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1592925935.jpeg",
            ]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'5',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1592925949.jpeg",
            ]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'5',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1593081387.jpeg",
            ]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'6',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1592925961.jpeg",
            ]);
            $user->profileDriver()->first()->documents()->create([
                "type"=>'6',
                "path"=>"https://logistica.wi-mobi.com/img/attachement/1592925970.jpeg",
            ]);
            $admin = User::create([
                'firstName' => 'admin firstName',
                'lastName' => 'lastName',
                'email' => 'admin@mail.com',
                'phone' => '123456258',
                'password' => bcrypt('logistica'),
                'roles' => json_encode(['admin'])
            ]);
            AdminRoles::updateOne(["roles" => ['test'=>123456]], $admin['id']);
            \App\Models\Settings::updateOne(
                [
                    'company_percent' => '15',
                    'abort_percent_client' => '10',
                    'abort_percent_captain' => '10',
                    'percent_from' => '0'
                ]);
        }
    }
}
