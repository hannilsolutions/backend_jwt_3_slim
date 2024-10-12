<?php

namespace App\Controllers;

use App\Models\SGPermisoAptitud; 
use App\Models\SGGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;


class SGPermisoAptitudController{
    protected  $customResponse;

    protected  $permisoAptitud;

    protected $generalidades;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->permisoAptitud = new SGPermisoAptitud(); 

         $this->generalidades = new SGGeneralidades();

         $this->validator = new Validator(); 
    }

    public function find_by_permiso_and_empleado(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id_permiso"=>v::notEmpty(),
            "id_user"=>v::notEmpty()
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }

         $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");
         $id_user = CustomRequestHandler::getParam($request , "id_user");

         $preguntas = new \stdClass();

         $permisoAptitud  = $this->permisoAptitud->where(["id_permiso" => $id_permiso])->where(["id_user" => $id_user])->get();

         if($permisoAptitud->count() == 0)
         {
            $generalidades = $this->generalidades->where(["item"=>"Aptitud"])->get();

            $json = ""; 
            foreach($generalidades as $item)
            {
                $json = $item->nombre; 
            }

            $aptitud = $this->permisoAptitud->create([
                "id_user" => $id_user,
                "id_permiso"=> $id_permiso,
                "estado" => "CREATE",
                "json" => $json
            ]);
        
            $get_permiso_aptitud = $this->permisoAptitud->where(["id_permiso" => $id_permiso])->where(["id_user" => $id_user])->get();

            foreach($get_permiso_aptitud as $item)
            {   $preguntas->id = $item->id_permiso_aptitud;
                $preguntas->id_permiso = $item->id_permiso;
                $preguntas->id_user = $item->id_user;
                $preguntas->estado = $item->estado;
                $preguntas->json = json_decode($item->json , true);
            }


         }else{

            
            foreach($permisoAptitud as $item)
            {   $preguntas->id = $item->id_permiso_aptitud;
                $preguntas->id_permiso = $item->id_permiso;
                $preguntas->id_user = $item->id_user;
                $preguntas->estado = $item->estado;
                $preguntas->json = json_decode($item->json , true);
            }

            
         }

         return $this->customResponse->is200Response($response , $preguntas);
    }

    public function update_aptitud(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id" => v::notEmpty()
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }

         $json = json_encode(CustomRequestHandler::getParam($request , "json"));
         $this->permisoAptitud->where(["id_permiso_aptitud"=>CustomRequestHandler::getParam($request , "id")])->update([
            "json" => $json
         ]);

         return $this->customResponse->is200Response($response  , "Actualizado");

    }
}
?>