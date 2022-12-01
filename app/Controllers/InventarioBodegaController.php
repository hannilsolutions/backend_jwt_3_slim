<?php

namespace App\Controllers;

use App\Models\InventarioBodegas;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioBodegaController
{

	  protected  $customResponse;

    protected  $bodega;

    protected  $validator;

     public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->bodega = new InventarioBodegas();

         $this->validator = new Validator();
    }


/**
 * ENDPOINT POST save*/
    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "bodega_nombre"=>v::notEmpty(), 
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->bodega->create([
           "bodega_nombre"=>CustomRequestHandler::getParam($request,"bodega_nombre")  
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

     /**
     * ENDPOITN GET*/
    public function list(Request $request,Response $response)
    {
       $list = $this->bodega->get();

        $this->customResponse->is200Response($response,$list);
    }

}


?>