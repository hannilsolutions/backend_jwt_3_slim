<?php
namespace App\Controllers;

class WsSendMessageController {

    protected $url;
    public function __construct()
    {
        $this->url = "http://send-ws.server.internetinalambrico.com.co";
    }
    

    public function send_text($phone , $msm)
    {
        $endpoint = $this->url."/ws";

        $data = array(
            "phone"=> $phone,
            "msm" => $msm
        );
        $ch = curl_init($endpoint);
        if ($ch === false) {
            // Manejar el error si curl_init falla
            die('Error inicializando cURL');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "x-token: a1e6a36ff08a7d428a480da419b90970463fa28277717e3fab4463e1ab971e22"
        ));

        $response = curl_exec($ch);
        /*if ($response === false) {
            // Manejar el error si curl_exec falla
            echo 'Error de cURL: ' . curl_error($ch);
        } else {
            // Manejar la respuesta exitosa
            $decoded_response = json_decode($response);
            // Hacer algo con $decoded_response si es necesario
            echo 'Respuesta exitosa: ' . $response;
        }*/

        curl_close($ch);
    }
}



?>