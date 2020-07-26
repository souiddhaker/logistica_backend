<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Trip;
use App\Models\Address;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Notif;
class OneUserSeeder extends Seeder
{

    public function createTrip(int $userID,int $driverID = null,string $status)
    {
        $trip1 = Trip::create([
            'status'=>$status,
            'total_price'=>20,
            'nbr_luggage'=>2,
            'type_car_id'=>1,
            'driver_note'=>'driver note',
            'user_id'=>$userID,
            'pickup_at'=>'2020-06-12 08:00:00',
            'driver_id'=>$driverID,
            'route'=>"yehyEiq~`AlAhBk@lAvAlE~AfFoAtA~ElOp@vBSNgAz@AAAAA?CAIBGHDT~Sto@tKv]Bz@InAY|@oAdBsAfAUDQ\B\PRT@JGpBd@tKvGzA|@pBn@r@P~BNlDGdCk@xAm@~AaAxCeDzEyGd@MTQDa@@UvAwBnJiMrDoDxD{CpJqFzMaHvDcAjEWlHl@rLpBlCRdCEvP}BzNsBvDs@`FcBrDmBxKaIvHkFrEoBbD{@tDg@|Qc@fEo@rEkADB\GDKbDqB`KkI~JuIxBuA`@@f@PbC`BrA`BlBvDfA|BfC`EbBpCjClGjExIdCzDvDvH^x@Tf@n@tAl@rAt@lB`BdGj@fAbACdBS`C@nEpCfHfD|F`CrHxBhCr@p@ZJL^ALo@AEzBqD~BgDpCwDrFoIhHgP|GyOvGqN`@}ALsB\cG~@eFhD{JpH_W|EiOpBmOv@uJpDkXt@_DrByJ`AoGXoD?gDJgEd@kDtByK`@uDD}BJu@n@oAHQxA{Bz@cBfBoIx@kE|@uI^}I@yDWaHCgDNyDBADCBG@C|F_BhEgBhJsDfHkC~t@mW`RyGnGqB|De@nDV|MhCjCd@rAV`DDhD@tL?dCCzFGzFGtEa@nCeArAw@lAeApBmC|@mBn@uBv@_GHiFj@_NnBkRVyAd@eB`ByDfBkCzCsCdAm@p@]lBq@fC_@jABjCOtJm@|BWDBLBLEFIzGqAjHw@t@StEa@`[iCbn@yExPqArHo@dCk@~BgA~@e@|BuB|@{@hBgClBsEf@cB`AmGhBmh@nAaO~@eFD?JEDK?]AEAAjBeKnCeMfAeJIuEa@qEBIhAe@jLmDnNiCnPoCnUgDzNaCvLaCrBk@pKcFnFiChNwGnMyFbKuDnO{EzCYrEMrKu@bD]pFqA|Bc@vH_BxW{F~OyBlBk@zF}AjF_AfJkAvIgBhK_CxRgC`Nm@vHm@bIw@dAE~BP~NrCpE~@bEx@tHx@tC`@DJLFVGF]CMAEZcBnDcJtFmRhEaMtFsRtMo[vK__@dAkDb@eCbBgPtAqLvCgRfByH`BkGn@cF\aIbAmI`@}EVaNd@yJXkJNsf@n@uJf@}F`@_LZiINeAPoA^}Cl@wHLkD?cDSeHNmKp@_P\}FjBcQ|AiLxGmb@~GyZrCuN|DcQtAuF`EoJhCgNj@yEVgGh@cGhBeQxBsTfAoWnA}Mn@}DdA}Dp@gC^{BJoDhBaMNo@dBcLBcCGcFlBwX@MfCPl@@A}BImCC_@|@a@Lm@V}AT}@n@DpACx@KN?"]);
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

    public function createNotif(int $id)
    {
        Notif::create(['title'=>['en'=>'Your booking #1205 has been can...','ar'=>'دعوة الأصدقاء - احصل على 3 كوبونات لكل منهم!'],
            'type'=>['en'=>'System','ar'=>'النظام'],
            'icon'=>'https://logistica.wi-mobi.com/img/icon/icon.png',
            'description' => ['en'=>'details','ar'=>'تفاصيل'],
            'user_id' => $id
        ]);
        Notif::create(['title'=>['en'=>'Invite friends - Get 3 coupons each!','ar'=>'دعوة الأصدقاء - احصل على 3 كوبونات لكل منهم!'],
            'type'=>['en'=>'Promotion','ar'=>'النظام'],
            'icon'=>'https://logistica.wi-mobi.com/img/icon/icon.png',
            'description' => ['en'=>'details','ar'=>'تفاصيل'],
            'user_id' => $id
        ]);
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(4);
        $driver = User::find(8);
        foreach (range(1, 15) as $i) {
            $this->createTrip($user->id,$driver->id,"-1");
            $this->createTrip($user->id,null,"0");
            $this->createTrip($user->id,$driver->id,"1");
            $this->createTrip($user->id,$driver->id,"2");
            $this->createTrip($user->id,$driver->id,"3");
//            $this->createNotif($driver->id);
        }
    }
}
