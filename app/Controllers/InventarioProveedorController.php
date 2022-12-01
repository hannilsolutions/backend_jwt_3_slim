<?php

namespace App\Controllers;

use App\Models\InventarioProveedor;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioProveedorController
{

	protected  $customResponse;

   	protected  $proveedor;

    protected  $validator;

     public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->proveedor = new InventarioProveedor();

         $this->validator = new Validator();
    }


/**
 * ENDPOINT POST save*/
    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
            "proveedor_name"=> v::notEmpty(),
     		"proveedor_nit"=> v::notEmpty(),
     		"proveedor_celular"=> v::notEmpty(),
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->proveedor->create([
             "proveedor_name"=>CustomRequestHandler::getParam($request,"proveedor_name"),
            "proveedor_nit"=>CustomRequestHandler::getParam($request,"proveedor_nit"),
            "proveedor_celular"=>CustomRequestHandler::getParam($request,"proveedor_celular")
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

     /**
     * ENDPOITN GET*/
    public function list(Request $request,Response $response)
    {
       $list = $this->proveedor->get();

        $this->customResponse->is200Response($response,$list);
    }

}


?>







