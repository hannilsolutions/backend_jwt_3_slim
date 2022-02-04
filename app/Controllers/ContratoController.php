<?php

namespace App\Controllers;
 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class ContratoController
{
    protected $customResponse;

    protected $contrato;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        //$this->contrato = new Contrato();

        $this->validator =  new Validator();
    }

    /**
     * findByCus
     * Get
     */
    public function findByCus(Request $request , Response $response , $id)
    {
        
        $findByCus = $this->apiControl($id["id"]);
        $responseMessage = $findByCus;
        $this->customResponse->is200Response($response,$responseMessage);

    }

    public function ApiControl($id)
    {
        $data = array(
            "contrato"=> $id,
            "key" => 'f24f0aaa81db035965e65f60c5e54c41',
            "m" => 4,
            "title" => 'findByCus'
        );
        $ch =   curl_init("http://http://131.221.41.20:8050/api/api_internet/v2/public/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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