<?php

namespace App\Controllers;

use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class FacturaController
{
    protected $customResponse;
 
    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->validator =  new Validator();
    }

    public function findByOne(Request $request, Response $response)
    {
        $this->validator->validate($request , [
            "factura" => v::notEmpty(),
            "id_servicio" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response,$responseMessage);
        } 

        $findByOneControl = $this->findByOneApiControl(CustomRequestHandler::getParam($request, "factura") , CustomRequestHandler::getParam($request , "id_servicio"));
        
        $responseMessage = $findByOneControl;
      
        $this->customResponse->is200Response($response,$responseMessage);
        
    }

    public function findByOneApiControl($factura, $servicio)
    {
        $data = array(
            "factura"=> $factura,
            "id_servicio" => $servicio,
            "key" => 'f24f0aaa81db035965e65f60c5e54c41',
            "m" => 4,
            "title" => 'findByFactura'
        );
        $ch =   curl_init("http://131.221.41.20:8050/api/api_internet/v2/public/");
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