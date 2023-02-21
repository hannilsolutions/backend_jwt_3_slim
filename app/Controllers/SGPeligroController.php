<?php

namespace App\Controllers;

use App\Models\SGPeligros;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPeligroController
{

    protected  $customResponse;

    protected  $sgPeligro;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->sgPeligro = new SGPeligros();

         $this->validator = new Validator();
    }

    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "nombre"=>v::notEmpty(),
           "consecuencias"=>v::notEmpty(),
           "id_empresa"=>v::notEmpty(),
           "id_clasificacion"=>v::notEmpty() 
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->sgPeligro->create([
           "nombre"=>CustomRequestHandler::getParam($request,"nombre"),
           "consecuencias"=>CustomRequestHandler::getParam($request,"consecuencias"),
           "id_empresa"=>CustomRequestHandler::getParam($request,"id_empresa"),
           "id_clasificacion"=>CustomRequestHandler::getParam($request,"id_clasificacion"),
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    } 

    public function deleteById(Request $request , Response $response , $id)
    {
        $deleteById = $this->sgPeligro
                            ->where(["id_peligro" => $id])
                            ->delete();
        $responseMessage="eliminado";

        $this->customResponse->is200Response($response , $responseMessage);
    }


    public function list(Request $request,Response $response)
    {
        $this->validator->validate($request,[
            "id_clasificacion"=>v::notEmpty(),
            "id_empresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $getList =  $this->sgPeligro
                        ->selectRaw("han_sg_peligros.id_peligro, 
                                    han_sg_peligros.nombre,
                                    han_sg_peligros.consecuencias,
                                    han_sg_peligros.id_empresa,
                                    han_sg_peligros.id_clasificacion,
                                    han_sg_clasificacion.nombre as nombreClasificacion")
                        ->leftjoin("han_sg_clasificacion" ,"han_sg_clasificacion.id_clasificacion", "=" ,"han_sg_peligros.id_clasificacion")
                        ->where(["han_sg_peligros.id_clasificacion" => CustomRequestHandler::getParam($request , "id_clasificacion")])
                        ->where(["han_sg_peligros.id_empresa"   => CustomRequestHandler::getParam($request , "id_empresa")])
                        ->get();

        $this->customResponse->is200Response($response,$getList);
    }
    /**
     * ENDPOIN DELETE inactivar enviar 0*/
    public function inactive(Request $request , Response $response , $id)
    {
        $this->sgPeligro
                ->where(["id_peligro" => $id])
                ->update(
                    ["estado" => 0]
                );
        $update = "Inactivado con éxito";

        $this->customResponse->is200Response($request , $update);
    }

    /**
     * ENDPOIN DELETE cerrar permiso*/
    public function cerrado(Request $request , Response $response , $id)
    {
        $this->sgPeligro
                ->where(["id_peligro"] => $id)
                ->update([
                    "estado" => 2
                ]);
        $updated = "Cerrado con éxito";

        $this->customResponse->is200Response($request , $update);

    }
}

?>