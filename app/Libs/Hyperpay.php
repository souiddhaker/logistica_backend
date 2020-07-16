<?php


namespace App\Libs;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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
        try {
            unset($params['type']);

            $result = $client->post( $this->url, [
                'form_params'    =>
                    $params
                ,
                'headers' => [
                    'Authorization' => 'Bearer '.env( 'HYPER_PAY_TOKEN'),
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                ],
            ] );
        }catch (ClientException $e){
            return null;
        }


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
        try {
            $url = 'https://test.oppwa.com/v1/checkouts'.'/'.$params['checkout_id'].'/payment'.'?entityId='.$params['entityId'];

            $result = $client->get( $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.env( 'HYPER_PAY_TOKEN'),
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                ],
            ] );
        }catch (ClientException $e){
            return $e->getMessage();
        }

        return json_decode( $result->getBody(), true );
    }
}
