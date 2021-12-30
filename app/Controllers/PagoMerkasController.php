<?php

namespace App\Controllers;

use App\Models\PagoMerkas;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class PagoMerkasController
{

    protected  $customResponse;

    protected  $pagoMerkas;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->pagoMerkas = new PagoMerkas();

         $this->validator = new Validator();
    }

    #guardar datos
    public function save(Request $request, Response $response){

        #validar campos vacios
        $this->validator->validate($request,[
            "id_servicio_rc"=>v::notEmpty(),
            "rc"=>v::notEmpty(),
            "valor"=>v::notEmpty(),
            "celular"=>v::notEmpty()
            
         ]);
         if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        #validamos si ya existe un registro por id_servicio_rc

        if($this->verifyExistTransaccion(CustomRequestHandler::getParam($request, "id_servicio_rc")))
        {
            $responseMessage = "rc ya creada";
            
            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->pagoMerkas->create([
            "id_servicio_rc"=>CustomRequestHandler::getParam($request,"id_servicio_rc"),
            "rc"=>CustomRequestHandler::getParam($request,"rc"),
            "valor"=>CustomRequestHandler::getParam($request,"valor"),
            "celular"=>CustomRequestHandler::getParam($request,"celular"),
            "fecha"=>CustomRequestHandler::getParam($request , "fecha"),
            "log"=>date("Y-m-d H:i:s").": creado desde servidor"
         ]);
 
         $responseMessage = "creado";
 
         return $this->customResponse->is200Response($response,$responseMessage);
    }

    #consulta de todos los registros
    public function all(Request $request , Response $response)
    {
        $this->validator->validate($request ,[
            "fecha"=>v::notEmpty()
        ] );
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response, $responseMessage);
        }

        $pagosMerkas = $this->pagoMerkas->where(["fecha"=> CustomRequestHandler::getParam($request , "fecha")])->get();
        
        if($pagosMerkas == false){

            $responseMessage = "sin registros";
            
            return $this->customResponse->is400Response($response , $responseMessage);
        }

        return $this->customResponse->is200Response($response , $pagosMerkas);
    }

    #consulta de valor de todos los registros
    public function countPagos(Request $request , Response $response )
    {
        $this->validator->validate($request , [
            "fecha"=>v::notEmpty() 
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        $countPagos = $this->pagoMerkas->where(["fecha" => CustomRequestHandler::getParam($request,"fecha")])->count();
        if($countPagos == false ){

            $responseMessage = "sin registros";

            return $this->customResponse->is400Response($response, $responseMessage);
        }
        return $this->customResponse->is200Response($response , $countPagos);
    }

    
    #validamos si una transaccion ya existe
    public function verifyExistTransaccion($transaccion)
    {
        $count = $this->pagoMerkas->where(["id_servicio_rc"=>$transaccion])->count();
        if($count==false)
        {
            return false;
        }
        return true;
    }
 

}