<?php
namespace App\Controllers;

class WsSendMessageController {

    protected $url;
    public function __construct()
    {
        $this->url = "http://send-ws.server.cableytv.com";
    }
    

    public function send_text($phone , $msm)
    {
        $endpoint = $this->url."/send-message";

        $data = array(
            "phone"=> $phone,
            "msm" => $msm
        );
        $ch =   curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-token: a1e6a36ff08a7d428a480da419b90970463fa28277717e3fab4463e1ab971e22"
        ));
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        if($response->success==false) {
                return false;
        }else{
                return $response->data;
        }
    }
}



?>