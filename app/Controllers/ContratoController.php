<?php

namespace App\Controllers;
 
use App\Models\Contrato;
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

        $this->contrato = new Contrato();

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

    public function preferenciaFactura(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id_contrato"=>v::notEmpty(),
            "preferencia_factura"=>v::notEmpty()
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }
         $verifyExist = $this->verifyExist(CustomRequestHandler::getParam($request , "id_contrato"));

         if($verifyExist != false)
         {
            $responseMessage = "duplicado";
            return $this->customResponse->is400Response($response , $responseMessage);
         }

         $this->contrato->create([
        "id_contrato"=>CustomRequestHandler::getParam($request,"id_contrato"),
         "preferencia_factura"=>CustomRequestHandler::getParam($request,"preferencia_factura"),
         "observacion"=>CustomRequestHandler::getParam($request,"observacion"),
         ]);
 
         $responseMessage = "creado";
 
         $this->customResponse->is200Response($response,$responseMessage);
    }
    public function verifyExist($contrato){

        $count = $this->contrato->where(["id_contrato"=>$contrato])->count();

        if($count==false)
        {
            return false;
        }
        return true;
    }
}


?>