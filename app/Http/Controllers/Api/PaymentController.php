<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Result;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    //

    public function addCard()
    {
        $res = new Result();

        \Stripe\Stripe::setApiKey('pk_test_oeS9emnzVKWaIR9jckSkGRj0');

       $token =  \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => 2021,
                'cvc' => '314',
            ],
        ]);
       if (!$token){
           $res->fail('Error on creating credit card');
           return response()->json($res,200);
       }

        $user = Auth::user();
       $card = new Card();
        $card->card_brand = $token->card->brand;

        $card->card_last_four = $token->card->last4;

        $card->stripe_id = $token->id;
        $card->status = $token->used;
        $user = $user->cards()->save($card);
        $response = [];
        $response['user'] = $user;
        $response['card'] = $card;

        $res->success(['card_id'=>$card->id]);
        return response()->json($res,200);
    }

    public function getCardsByUser()
    {
        $user = Auth::id();
        $listCards =  Card::where('user_id',$user)->get();

        $res = new Result();

        $res->success($listCards);

        return response()->json($res,200);
    }

    public function countAmount(array $listTrip)
    {
        $response = [];
        $companyPercent = 0;
        $amount = 0;
        foreach ($listTrip as $trip){
            $amount = $amount + $trip['total_price'];
            $companyPercent = $companyPercent + $trip['company_percent'];
        }
        $response['total'] = $amount;
        $response['company_percent'] = $companyPercent;
        return $response;
    }
    public function resume()
    {
        $res = new Result();
        $listTripCash = Trip::where('payment_method', '=', null)
            ->where('driver_id', '=',Auth::id())
            ->get();

        $listOnLine = Trip::where('payment_method', '!=', null)
            ->where('driver_id', '=',Auth::id())
            ->get();

        $response['cash'] = $this->countAmount($listTripCash->toArray());

        $response['payment'] = $this->countAmount($listOnLine->toArray());
        $response['payment']['claim'] = 0;
        $response['payment']['debt'] = 0;
        $res->success($response);
        return response()->json($res,200);
    }
}
