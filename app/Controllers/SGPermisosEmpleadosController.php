<?php

namespace App\Controllers;

use App\Models\SGPermisoEmpleado;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPermisosEmpleadosController
{

    protected $customResponse;

    protected $sgPermisoEmpleado;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermisoEmpleado = new SGPermisoEmpleado();

        $this->validator = new Validator();

    }

    /**
     * ENPOINT POST 
     */
    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso_trabajo" => v::notEmpty(),
            "id_user" => v::notEmpty(),
            "id_empresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        if($this->verifyExistEmpleado(CustomRequestHandler::getParam($request , "id_user")))
        {
            $responseMessage = "Empleado ya seleccionado";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->sgPermisoEmpleado->create([
            "id_permiso_trabajo" => CustomRequestHandler::getParam($request , "id_permiso_trabajo") , 
            "id_user"   => CustomRequestHandler::getParam($request , "id_user"),
            "id_empresa" => CustomRequestHandler::getParam($request , "id_empresa")
        ]);

        $responseMessage  = "creado";

        $this->customResponse->is200Response($response , $responseMessage);
    }
    /**
     * ENDPOINT GET empleado de permiso asignado
     * select users.user , users.email from han_sg_permisos_empleados
    *inner join users on users.id = han_sg_permisos_empleados.id_user

    *where han_sg_permisos_empleados.id_permiso_trabajo = 33
     */

    public function findByIdpermiso(Request $request , Response $response , $id)
    {
        $getFindByIdpermiso = $this->sgPermisoEmpleado->selectRaw("han_sg_permisos_empleados.id_permisos_empleado, users.user , users.email")
                                                        ->join("users" , "users.id" , "=" , "han_sg_permisos_empleados.id_user")
                                                        ->where(["id_permiso_trabajo" => $id])
                                                        ->get();

        $this->customResponse->is200Response($response , $getFindByIdpermiso);
    }

    public function deleteById(Request $request , Response $response , $id)
    {
        $this->sgPermisoEmpleado->where(["id_permisos_empleado" => $id])->delete();

        $responseMessage = "Eliminado";

        $this->customResponse->is200Response($response  , $responseMessage);
    }

    /*
    *validar si empleado ya se encuentra en permiso de trabajo
    */
    public function verifyExistEmpleado($user )
    #SELECT * FROM han_sg_permisos_empleados 

    #inner join han_sg_permiso_trabajo on han_sg_permiso_trabajo.id_permiso = han_sg_permisos_empleados.id_permiso_trabajo

    #WHERE han_sg_permisos_empleados.id_user = 1 and han_sg_permiso_trabajo.estado = 1
    {
        $count = $this->sgPermisoEmpleado->join("han_sg_permiso_trabajo" , "han_sg_permiso_trabajo.id_permiso" , "=" , "han_sg_permisos_empleados.id_permiso_trabajo")
                                            ->where(["han_sg_permisos_empleados.id_user" => $user])
                                            ->where("han_sg_permiso_trabajo.estado" , "=" , "1")                                            
                                            ->count();
        if($count == 0)
        {
            return false;
        }

        return true;
    }

    /*
    *ENPOINT GET
    */
    /*SELECT 
            han_sg_permisos_empleados.id_permisos_empleado,
            han_sg_permiso_trabajo.fecha_inicio,
            han_sg_permiso_trabajo.hora_inicio,
            han_sg_permiso_trabajo.prefijo,
            han_sg_permiso_trabajo.lugar_de_trabajo,
            han_sg_permiso_trabajo.indicativo,
            han_sg_permiso_trabajo.id_permiso
            FROM han_sg_permisos_empleados

            inner join han_sg_permiso_trabajo on han_sg_permiso_trabajo.id_permiso = han_sg_permisos_empleados.id_permiso_trabajo

            where han_sg_permiso_trabajo.estado = 1 and han_sg_permisos_empleados.id_user = 22 */

    public function findByEmpleado(Request $request , Response $response , $id)
    {
        $getFindByEmpleado = $this->sgPermisoEmpleado->selectRaw("han_sg_permisos_empleados.id_permisos_empleado,
            han_sg_permiso_trabajo.fecha_inicio,
            han_sg_permiso_trabajo.hora_inicio,
            han_sg_permiso_trabajo.prefijo,
            han_sg_permiso_trabajo.lugar_de_trabajo,
            han_sg_permiso_trabajo.indicativo,
            han_sg_permiso_trabajo.id_permiso")
            ->join("han_sg_permiso_trabajo" , "han_sg_permiso_trabajo.id_permiso" , "=" , "han_sg_permisos_empleados.id_permiso_trabajo")
            ->where(["han_sg_permisos_empleados.id_user" => $id])
            ->where("han_sg_permiso_trabajo.estado" , "=" , "1")
            ->get();
        
        $this->customResponse->is200Response($response , $getFindByEmpleado);
    }


}