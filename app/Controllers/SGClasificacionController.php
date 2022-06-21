<?php

namespace App\Controllers;

use App\Models\SGClasificacion;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGClasificacionController
{

    protected  $customResponse;

    protected  $sgClasificacion;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->sgClasificacion = new SGClasificacion();

         $this->validator = new Validator();
    }

    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "nombre"=>v::notEmpty() 
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->sgClasificacion->create([
           "nombre"=>CustomRequestHandler::getParam($request,"nombre")
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    } 

    public function list(Request $request,Response $response)
    {
        $getList =  $this->sgClasificacion->get();

        $this->customResponse->is200Response($response,$getList);
    }
}