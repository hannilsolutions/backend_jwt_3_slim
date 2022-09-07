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

    /**
     * ENDPOINT POST => save*/
    public function save(Request $request, Response $response)
    {

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

    /*
    *POST BETWEEN buscar por fechas
    */
    public function findByBetween(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "valor1"    => v::notEmpty(),
            "valor2"    => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $getBetweenPagos = $this->pagoMerkas
                                ->whereBetween('fecha', [CustomRequestHandler::getParam($request , "valor1"), CustomRequestHandler::getParam($request , "valor2")])
                                ->get();

        $this->customResponse->is200Response($response , $getBetweenPagos);
    }

    /**
     * ENDPOINT POST => all by fecha*/
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

    /**
     * ENDPOINT POST => conteo de pagos en tal fecha*/

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
    /**
     *ENDPOINT PACTH =>  update estado 1 = enviado correctamente, 2 error en el cargue NO USADO
     **/
     
    public function updateReciboCaja(Request $request , Response $response , $id)
    {
        $this->validator->validate($request , [
            "log" => v::notEmpty(),
            "estado" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response, $responseMessage);
        }
        
        $this->pagoMerkas->where(['id' => $id])->update([
            "log" => CustomRequestHandler::getParam($request , "log"),
            "estado" => CustomRequestHandler::getParam($request , "estado"),
        ]);

        $responseMessage = "actualizado";

        return $this->customResponse->is200Response($response, $responseMessage);
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

    /**
     * ENDPOINT GET => consultar para cargar estado 0 y dia */ 
    public function countEstado(Request $request , Response $response)
    {
        
        $dia = date("Ymd");
        
        #validar si existe un valor
        $verificarEstado = $this->verifyCountEstado($dia);

        $registrosSendMerkas = $this->pagoMerkas->where([
            ['fecha' , '=' , $dia],
            ['estado' , '=' , '0']])->get();

        return $this->customResponse->is200Response($response, $registrosSendMerkas);
    }

    #validar count registros a enviar a merkas
    public function verifyCountEstado($dia)
    {
        $countEstado = $this->pagoMerkas->where([
            ['fecha' , '=' , $dia ],
            ['estado' ,'=', '0']
            ])->count();

        if($countEstado == false)
        {
            return false;

        }
            return true;
        
    }

   

}