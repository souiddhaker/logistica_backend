<?php


namespace App\Libs;


use GuzzleHttp\Client;

class Hyperpay
{
    private $url = "";


    function __construct() {
        $this->url = env( 'HYPER_PAY_BASE_URL');
    }

    /**
     * CURL request to Hyper Pay servers
     * @param $params
     *
     * @return bool|string
     */
    public function getAccessId( $params )
    {
        $client = new Client();
        $result = $client->post( $this->url, [
            'form_params'    =>
                $params
            ,
            'headers' => [
                'Authorization' => 'Bearer '.env('HYPER_PAY_TOKEN'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ] );

        return json_decode( $result->getBody(), true );
    }

    /**
     * CURL request to Hyper Pay servers
     * @param $params
     *
     * @return bool|string
     */
    public function getPaymentStatus(  $params )
    {
        $client = new Client();
        $result = $client->get( 'https://test.oppwa.com/v1/checkouts'.'/'.$params['checkout_id'].'/payment'.'?entityId='.$params['entityId'], [
            'headers' => [
                'Authorization' => 'Bearer '.env('HYPER_PAY_TOKEN'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ] );

        return json_decode( $result->getBody(), true );
    }
}
