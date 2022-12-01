<?php

namespace App\Controllers;

use App\Models\InventarioIngresos;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioIngresoController
{

  protected  $customResponse;

    protected  $ingreso;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->ingreso = new InventarioIngresos();

         $this->validator = new Validator();
    }

    /**
 * ENDPOINT POST save*/
    public function save(Request $request,Response $response)
    {
    	$this->validator->validate($request , [

    		"proveedor_id" => v::notEmpty(),
    		"bodega_id"	=> v::notEmpty(),
    		"usuario_id" => v::notEmpty(),
    		"ingreso_factura" => v::notEmpty(),
    		"ingreso_tipo" => v::notEmpty(),


    	]);

    	if ($this->validator->failed()) {
    		
    		$responseMenssage = $this->validator->errors;

    		return $this->customResponse->is400Response($response , $responseMenssage);
    	}

    	try{

    		$this->ingreso->create([ 
    			"ingreso_fecha" => date("Y-m-d"),
			    "ingreso_hora" => date("H:m:s"),
			    "proveedor_id" => CustomRequestHandler::getParam($request , "proveedor_id"),
			    "bodega_id" => CustomRequestHandler::getParam($request , "bodega_id"),
			    "ingreso_valor" => CustomRequestHandler::getParam($request , "ingreso_valor"),
			    "usuario_id" => CustomRequestHandler::getParam($request , "usuario_id"),
			    "ingreso_factura" => CustomRequestHandler::getParam($request , "ingreso_factura"), 
			    "ingreso_tipo" => CustomRequestHandler::getParam($request , "ingreso_tipo"), 
    		]);

    		$responseMenssage = "creado";

    		$this->customResponse->is200Response($response , $responseMenssage);


    	}catch(QueryException $e){

    		$this->customResponse->is400Response($response , $e);
    	}

    }

    /**
     * ENDPOINT GETLIST*/
    public function list(Request $request , Response $response)
    {
      $list = $this->ingreso->get();

      $this->customResponse->is200Response($response , $list);
    }

    /**
     * ENDPOTIN GET FINBYID*/
    public function findById(Request $request , Response $response , $id)
    {
      $findById = $this->ingreso->where(["ingreso_id" => $id]);

      $this->customResponse->is200Response($response  , $findById);
    }



} 


?>