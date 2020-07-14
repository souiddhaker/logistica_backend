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
//        return $params;
        $result = $client->post( $this->url, [
            'form_params'    =>
                $params
            ,
            'headers' => [
                'Authorization' => 'Bearer OGE4Mjk0MTc0ZDA1OTViYjAxNGQwNWQ4MjllNzAxZDF8OVRuSlBjMm45aA==',
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
        ] );
//        'Authorization' => 'Bearer '.env('HYPER_PAY_TOKEN'),

        return json_decode( $result->getBody(), true );
    }
}
