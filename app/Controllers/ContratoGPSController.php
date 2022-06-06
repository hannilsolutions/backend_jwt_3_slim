<?php

namespace App\Controllers;
 
use App\Models\ContratoGps;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class ContratoGPSController
{
    protected $customResponse;

    protected $contratoGps;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->contratoGps = new ContratoGps();

        $this->validator =  new Validator();
    }

    /**
     * contratoGps -> latitud - longitud  
     * post
     */
       

    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id_contrato"   =>  v::notEmpty(),
            "key"           =>  v::notEmpty(),
            "longitud"      =>  v::notEmpty(),
            "latitud"       =>  v::notEmpty()
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }
         $getVerifyKey = $this->verifyKey(CustomRequestHandler::getParam($request , "key"));

         if($getVerifyKey != true)
         {
            $responseMessage = "error key";
            return $this->customResponse->is400Response($response , $responseMessage);
         }

         $this->contratoGps->create([
        "id_contrato"=>CustomRequestHandler::getParam($request,"id_contrato"),
         "latitud"=>CustomRequestHandler::getParam($request,"latitud"),
         "longitud"=>CustomRequestHandler::getParam($request,"longitud"),
         ]);
 
         $responseMessage = "creado";
 
         $this->customResponse->is200Response($response,$responseMessage);
    }

    public function verifyKey($key)
    {
        //HannilSolutions
        $k = "3e1d7ed98e94366975582f41f77a0bc9442a288da87d164bdc9fef66e57de70f";
        if($key == $k)
        {
            return true;
        }else{

            return false;
        }
    }
     
}


?>