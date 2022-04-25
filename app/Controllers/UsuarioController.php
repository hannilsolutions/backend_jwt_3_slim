<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class UsuarioController
{

    protected  $customResponse;

    protected  $usuario;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->usuario = new Usuario();

         $this->validator = new Validator();
    }

     

    public function List(Request $request,Response $response , $id)
    {
        $usuarios = $this->usuario->simplePaginate($id);
        $count = $this->usuario->count();
        $responseMenssage = ["total" => $count , "usuarios" => $usuarios];
        $this->customResponse->is200Response($response, $responseMenssage);
    }


     

}