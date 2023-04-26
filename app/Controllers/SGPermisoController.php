<?php

namespace App\Controllers;

use App\Models\SGPermiso;
use App\Models\SGEmpresa;
use App\Models\SGPermisoEmpleado;
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

    protected $sgEmpresa;

    protected $empleadoPermiso;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermiso = new SGPermiso();

        $this->validator = new Validator();

        $this->sgEmpresa = new SGEmpresa();

        $this->empleadoPermiso = new SGPermisoEmpleado();
    }
    /*
    *ENDPOINT: POST
    */
    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "fecha_inicio" => v::notEmpty(),
            "hora_inicio" => v::notEmpty(),
            "lugar_de_trabajo" => v::notEmpty(),
            "id_usuario" => v::notEmpty(),
            "id_empresa" => v::notEmpty(),
            "id_permiso_trabajo" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMenssage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMenssage);
        }

        //validar si ya esta en un permiso relacionado, de lo contratio
        if ($this->validarExistPermiso(CustomRequestHandler::getParam($request , "id_usuario"))) {

            $responseMessage = "ya registrado en un permiso";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        //validar si tiene un permiso creado por el user 
       /* if($this->validarCreatePermiso(CustomRequestHandler::getParam($request , "id_usuario")))
        {
            $responseMenssage = "Ya tiene un permiso creado";

            return $this->customResponse->is400Response($response , $responseMenssage);

        }*/

        $indicativo = $this->findByIndicativo(CustomRequestHandler::getParam($request , "id_empresa"));

        $prefijo    = $this->findByPrefijoEmpresa(CustomRequestHandler::getParam($request , "id_empresa"));

        $insert = $this->sgPermiso->create([
            "fecha_inicio" => CustomRequestHandler::getParam($request , "fecha_inicio"),
            "hora_inicio" => CustomRequestHandler::getParam($request , "hora_inicio"),
            "lugar_de_trabajo" => CustomRequestHandler::getParam($request , "lugar_de_trabajo"),
            "id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
            "id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
            "estado" => "1",
            "prefijo" => $prefijo, 
            "indicativo" => $indicativo,
            "id_permiso_trabajo" => CustomRequestHandler::getParam($request , "id_permiso_trabajo"),
        ]);

        $responseMenssage = $insert->id;

        $this->customResponse->is200Response($response , $responseMenssage) ;
    }
    //validar si ha creado un permiso
    public function validarCreatePermiso($idusuario)
    {
        $count = $this->sgPermiso->where("han_sg_permiso_trabajo.id_usuario" , "=" , $idusuario)->count();

        if($count == 0)
        {
            return false;
        }

        return true;
    }

    //validacion de si esta en un pérmiso ya creado
    public function validarExistPermiso($idusuario)
    {
        $count = $this->empleadoPermiso->join("han_sg_permiso_trabajo" , "han_sg_permiso_trabajo.id_permiso" , "=" , "han_sg_permisos_empleados.id_permiso_trabajo")
        ->where("han_sg_permisos_empleados.id_user", "=" , $idusuario)
        ->where("han_sg_permiso_trabajo.estado" , "=" , "1")->count();
        if ($count == 0) {
            
            return false;
        }
        
        return true;
    }

    //ENDPOINT:GET
    public function findByUsuarioOpen(Request $request , Response $response , $id)
    {
        $getFindByUsuarioOpen = $this->sgPermiso
                                    ->where(["id_usuario" => $id])
                                    ->where("estado" , "=" , "1")
                                    ->get();

        $this->customResponse->is200Response($response , $getFindByUsuarioOpen);
    }

    public function findByIndicativo($id_empresa)
    {
        $indicativo = 1;
 
        $getFindByIndicativo = $this->sgPermiso->selectRaw("indicativo")->where("id_empresa" , "=" , $id_empresa)->count();

        if($getFindByIndicativo > 0)
        {
            $indicativo = $getFindByIndicativo + 1;
        }

        return $indicativo;
    }

    public function findByPrefijoEmpresa($id_empresa)
    {
        $prefijo = '';

        $getFindByPrefijoEmpresa = $this->sgEmpresa->selectRaw("prefijo")->where("id_empresa" , "=" , $id_empresa)->get();

        foreach($getFindByPrefijoEmpresa as $item)
        {
            $prefijo = $item->prefijo;
        }

        return $prefijo;
    }
    /**
     * ENDPOINT GET findById*/
    public function findById(Request $request , Response $response , $id)
    {
        $getId = $this->sgPermiso->where(["id_permiso" => $id])->get();

        $this->customResponse->is200Response($response , $getId);
    }

    /**
     * ENDPOINT GET findbyidempresa all PARA ADMIN_ADMIN ADMIN_ST
     SELECT han_sg_permiso_trabajo.* ,
    tp.nombre as nombre_tipo,
    users.user FROM han_sg_permiso_trabajo 
    inner join han_sg_tipos_trabajo tp on tp.id_tipo =  han_sg_permiso_trabajo.id_permiso_trabajo
    inner join users on users.id = han_sg_permiso_trabajo.id_usuario

    WHERE han_sg_permiso_trabajo.id_empresa = 1
    */
    public function findByIdEmpresa(Request $request , Response $response , $id)
    {
        $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
            tp.nombre as nombre_tipo,
            users.user")
        ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
        ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
        ->where(["han_sg_permiso_trabajo.id_empresa"=>$id])->get();

        foreach($getList as $item)
        {
            $item->avance = $this->countAvance($item->id_permiso);
        }

        $this->customResponse->is200Response($response , $getList);
    }

    /***/
    private function countAvance($idpermiso)
    {
        $count = $this->empleadoPermiso->where("id_permiso_trabajo" , "=" , $idpermiso)->whereNotNull("firma")->count();
        
        
        
        if($count == 0)
        {
            return 0;

        }else{

            $coutEmpleados = $this->empleadoPermiso->where("id_permiso_trabajo" , "=" , $idpermiso)->count();

            return ($count/$countEmpleados)*100;
        }

    }

    /**
     * ENDPOINT GET findByActivoUsuario*/
    public function findByIdUsuarioActive(Request $request , Response $response , $id)
    {
        $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
            tp.nombre as nombre_tipo,
            users.user")
        ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
        ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
        ->join("han_sg_permisos_empleados as empleado" , "empleado.id_permiso_trabajo" , "=" , "han_sg_permiso_trabajo.id_permiso")
        ->where(["empleado.id_user"=>$id])
        ->where("han_sg_permiso_trabajo.estado" ,"=" , "1")->get();

        $this->customResponse->is200Response($response , $getList);
    }

    /**
     * ENDPOIN DELETE inactivar enviar 0*/
    public function inactive(Request $request , Response $response , $id)
    {
        $this->sgPermiso
                ->where(["id_permiso" => $id])
                ->update(
                    ["estado" => 0]
                );
        $update = "Inactivado con éxito";

        $this->customResponse->is200Response($response , $update);
    }

    /**
     * ENDPOIN DELETE cerrar permiso*/
    public function cerrado(Request $request , Response $response , $id)
    {
        $this->sgPermiso
                ->where(["id_permiso" => $id])
                ->update([
                    "estado" => 2
                ]);
        $updated = "Cerrado con éxito";

        $this->customResponse->is200Response($request , $update);

    }
}