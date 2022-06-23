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

/**
 * Lista de usuarios Get
 */

    public function List(Request $request,Response $response , $id)
    {
        $usuarios = $this->usuario
                ->offset($id["id"])
                ->limit(5)
                ->get();
        $count = $this->usuario->count();
        $responseMenssage = ["total" => $count , "usuarios" => $usuarios];
        $this->customResponse->is200Response($response, $responseMenssage);
    }
/*
    Buscar por nombre el usuario para like Get
*/

    public function findByName(Request $request, Response $response, $name)
    {
        $getFindByName = $this->usuario
                                ->where("user", "like", "%".$name["name"]."%")
                                ->get(); 
        $this->customResponse->is200Response($response , $getFindByName);
    }
/**
 * Eliminacion de usuario por id delete
 */
    public function deleteById(Request $request , Response $response , $id)
    {
        $delete = $this->usuario->where(["id"=>$id])->delete();
        $responseMenssage = "Eliminado";
        $this->customResponse->is200Response($response , $responseMenssage);
    }
/**
 * Actualización de Usuarios por id patch
 */
    public function updateById(Request $request,Response $response,$id)
    {

        $this->validator->validate($request,[
            "user"=>v::notEmpty(),
            "email"=>v::notEmpty()->email(),
            "role"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }


        $this->usuario->where(['id'=>$id])->update([
            "user"=>CustomRequestHandler::getParam($request,"user"),
            "email"=>CustomRequestHandler::getParam($request,"email"),
            "role"=>CustomRequestHandler::getParam($request,"role"),
            "active"=>CustomRequestHandler::getParam($request , "active")
        ]);
        $responseMessage = "Actualizado";

        $this->customResponse->is200Response($response,$responseMessage);
    }

     

}

?>