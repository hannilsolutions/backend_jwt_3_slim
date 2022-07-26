<?php

namespace App\Controllers;

use App\Models\SGPermiso;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGPermisoController
{
    protected $customResponse;

    protected $sgPermiso;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermiso = new SGPermiso();

        $this->validator = new Validator();
    }

    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "fecha_inicio" => v::notEmpty(),
            "hora_inicio" => v::notEmpty(),
            "lugar_de_trabajo" => v::notEmpty(),
            "id_usuario" => v::notEmpty(),
        ]);

        if($this->validator->failed())
        {
            $responseMenssage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMenssage);
        }

        $this->sgPermiso->create([
            "fecha_inicio" => CustomRequestHandler::getParam($request , "fecha_inicio"),
            "hora_inicio" => CustomRequestHandler::getParam($request , "hora_inicio"),
            "lugar_de_trabajo" => CustomRequestHandler::getParam($request , "lugar_de_trabajo"),
            "id_usuario" => CustomRequestHandler::getParam($request , "id_usuario")
        ]);

        $responseMenssage = "creado";

        $this->customResponse->is200Response($response , $responseMenssage) ;
    }
}