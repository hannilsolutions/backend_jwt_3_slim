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

    /**buscar usuario por nombre y empresa*/

    public function findByNameAndEmpresa(Request $request , Response $response )
    {
        $this->validator->validate($request , [
            "name" => v::notEmpty(),
            "id_empresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return  $this->customResponse->is400Response($response , $responseMessage);
        }
        $name = CustomRequestHandler::getParam($request , "name");
        $id_empresa = CustomRequestHandler::getParam($request , "id_empresa");

        $getFindByNameEmpresa = $this->usuario->where("user" , "like" , "%".$name."%")
                                ->where("id_empresa", "=" , $id_empresa)
                                ->get();

        $this->customResponse->is200Response($response , $getFindByNameEmpresa);                  
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

    /*
    *ENDPOINT GET id_empresa 
    */

    public function findByIdempresa(Request $request , Response $response  , $id)
    {
        $getFindByIdempresa = $this->usuario->where("id_empresa" , "=" , $id["id"])->get();

        $this->customResponse->is200Response($response , $getFindByIdempresa);
    }

    /*
    *ENDPOINT GET Generar firma digital
    */
    public function generateFirmaElectronica(Request $request , Response $response , $id)
    {
        $path = '/home/internet/public_html/apps/Files/usuarios/frmEOL/'.$id["id"];
        
        if (!is_dir($path)) 
        {
            mkdir($path, 0777, true);
        }

        $new_key_pair = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        openssl_pkey_export($new_key_pair, $private_key_pem);

        $details = openssl_pkey_get_details($new_key_pair);
        $public_key_pem = $details['key'];
         
         
        file_put_contents($path.'/private_key1.pem', $private_key_pem);
        file_put_contents($path.'/public_key1.pem', $public_key_pem); 

        $this->usuario->where(["id" => $id])->update([
                "private_key" => $private_key_pem,
                "public_key" => $public_key_pem,
        ]);

        $responseMessage = "firma creada";

        $this->customResponse->is200Response($response , $responseMessage);
    }

    /*
    *ENDPOINT GET user for id
    */
    public function findKeyById(Request $request , Response $response , $id)
    {
        $getFindKeyById = $this->usuario->selectRaw(
                            "private_key , public_key"
                            )->where(["id" => $id])->get();

        $this->customResponse->is200Response($response , $getFindKeyById);
    }

    /*
    *ENDPOINT PATCH UPDATED PASSWORD
    */

    public function updatedPassword(Request $request , Response $response , $id)
    {
        $this->validator->validate($request , [
            "password" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $passwordHash = $this->hashPassword(CustomRequestHandler::getParam($request,"password"));

        $this->usuario->where(["id" => $id])->update(["password" => $passwordHash] );

        $responseMessage = "contraseña actualizada";

        $this->customResponse->is200Response($response , $responseMessage);
    }

    //function para encriptar contraseña
    public  function hashPassword($password)
        
    {
            return password_hash($password,PASSWORD_DEFAULT);
        
    }


    /**POST BUSCAR POR NOMBRE Y EMPRESA */
    public function findNameAndEmpresa(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "query" => v::notEmpty(),
            "idEmpresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $list = $this->usuario->selectRaw('id, user , email, id_empresa')
                                ->where('user' , 'LIKE' ,  '%'.CustomRequestHandler::getParam($request , 'query').'%')
                                ->where(['id_empresa' => CustomRequestHandler::getParam([$request , 'idEmpresa'])])
                                ->where(['active' => 1])
                                ->get();

        $this->customResponse->is200Response($response , $list);


    }


         

}

?>