<?php

namespace App\Controllers;

use App\Models\InventarioArticulo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioArticuloController
{

    protected  $customResponse;

    protected  $articulo;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->articulo = new InventarioArticulo();

         $this->validator = new Validator();
    }

/**
 * ENDPOINT POST save*/
    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "nombre"=>v::notEmpty(),
           "codigo"=>v::notEmpty(),
           "unitario"=>v::notEmpty(),
           "categoria"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->articulo->create([
           "articulo_nombre "=>CustomRequestHandler::getParam($request,"nombre"),
            "articulo_codigo"=>CustomRequestHandler::getParam($request,"codigo"),
            "articulo_valor"=>CustomRequestHandler::getParam($request,"valor"),
            "articulo_unitario"=>CustomRequestHandler::getParam($request,"unitario"),
            "articulo_cantidad"=>CustomRequestHandler::getParam($request,"cantidad"),
            "articulo_categoria"=>CustomRequestHandler::getParam($request,"categoria"),
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    /**
     * ENDPOITN GET*/
    public function list(Request $request,Response $response)
    {
       $list = $this->articulo->get();

        $this->customResponse->is200Response($response,$list);
    }

/*
    public function getSingleGuest(Request $request,Response $response,$id)
    {

        $singleGuestEntry = $this->guestEntry->where(["id"=>$id])->get();

        $this->customResponse->is200Response($response,$singleGuestEntry);
    }

    public function editGuest(Request $request,Response $response,$id)
    {

        $this->validator->validate($request,[
            "name"=>v::notEmpty(),
            "email"=>v::notEmpty()->email(),
            "comments"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }


        $this->guestEntry->where(['id'=>$id])->update([
            "full_name"=>CustomRequestHandler::getParam($request,"name"),
            "email"=>CustomRequestHandler::getParam($request,"email"),
            "comment"=>CustomRequestHandler::getParam($request,"comments"),
        ]);
        $responseMessage = "guest entry data updated successfully";

        $this->customResponse->is200Response($response,$responseMessage);
    }

    public function deleteGuest(Request $request,Response $response,$id)
    {
        $this->guestEntry->where(["id"=>$id])->delete();

        $responseMessage = "guest entry data deleted successfully";

        $this->customResponse->is200Response($response,$responseMessage);
    }

    public function countGuests(Request $request,Response $response)
    {
        $guestsCount = $this->guestEntry->count();

        $this->customResponse->is200Response($response,$guestsCount);
    }*/

}

?>