<?php


namespace App\Libs;
use GuzzleHttp\Client;
use PHPUnit\Util\Exception;

class Sms
{
    /**
     * Sending sms
     * @param $to
     * @param $message
     *
     * @return bool|string
     */
    public function send( $to, $message,$date,$time ) {
        $fields = array(
            'numbers'   => $to,
            'message' => $message,
            'date' => $date,
            'time' => $time
        );

        return $this->sendSMS( $fields );
    }

    /**
     * CURL request to HiSMS
     * @param $fields
     *
     * @return bool|string
     */
    private function sendSMS( $fields ) {

        // Set POST variables
        $url = 'https://www.hisms.ws/api.php?send_sms&username=966550118877&password=aatt68956895&sender=Active-code&numbers='
            .$fields['numbers'].'&message='.$fields['message'].'&date='.$fields['date'].'&time='.$fields['time'];

        $client = new Client();
        try {
            $result = $client->get( $url);
        }catch (Exception $exception)
        {
            return false;
        }

        return $result->getBody()->getContents();

    }
}
