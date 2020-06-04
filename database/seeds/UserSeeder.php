<?php

use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\User;
use App\Models\Driver;
use App\Models\Notif;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //


        $listUser = User::all();
        foreach ($listUser as $user)
        {

            $userAddress = Address::where('user_id',$user->id)->get();
            if (count($userAddress) == 0)
            {
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
}
