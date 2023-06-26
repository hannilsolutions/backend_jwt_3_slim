<?php

namespace App\Controllers;

use App\Models\SGPermiso;
use App\Models\SGEmpresa;
use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\Usuario;
use App\Models\SGFirma;
use App\Models\SGDetalleFirmas;
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

    protected $empleadoGeneralidades;

    protected $usuario;

    protected $firmaEmpresa;

    protected $firmaJefes;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermiso = new SGPermiso();

        $this->validator = new Validator();

        $this->sgEmpresa = new SGEmpresa();

        $this->empleadoPermiso = new SGPermisoEmpleado();

        $this->usuario = new Usuario;

        $this->empleadoGeneralidades = new SGEmpleadoGeneralidades();

        $this->firmaEmpresa = new SGFirma();

        $this->firmasJefes = new SGDetalleFirmas();


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
        
        $resultado = 0;
        
        if($count != 0)
        {
             
            $coutEmpleados = $this->empleadoPermiso->where("id_permiso_trabajo" , "=" , $idpermiso)->count();

            $resultado =  ($count/$coutEmpleados)*100;

        }

        return $resultado;

    }

    /**
     * ENDPOINT GET findByActivoUsuario*/
    public function findByIdUsuarioActive(Request $request , Response $response )
    {
        //cambiar a post

        $idUsuario = CustomRequestHandler::getParam($request , "id_user");
        $estado = CustomRequestHandler::getParam($request , "estado");
        $fecha = CustomRequestHandler::getParam($request , "fecha");
        
        $datos_user = $this->usuario->where("id" , "=" , $idUsuario)->get();

        foreach($datos_user as $item)
        {
            $role = $item->role;

            $idEmpresa = $item->id_empresa;
        }

         if($role == "TECNICO_ST")
        {

            if(empty($fecha))
            {
                $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
            tp.nombre as nombre_tipo,
            users.user")
                    ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
                    ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
                    ->join("han_sg_permisos_empleados as empleado" , "empleado.id_permiso_trabajo" , "=" , "han_sg_permiso_trabajo.id_permiso")
                    ->where("empleado.id_user" , "=" , $idUsuario)
                    ->where("han_sg_permiso_trabajo.estado" ,"=" , "1")->get();

                    foreach($getList as $item)
                    {
                        $item->empleados = $this->findIntegrantes($item->id_permiso);
                        $cantidad = count($item->empleados);
                        $item->avance = $this->findEstadoFirmas($item->id_permiso , $cantidad , $idEmpresa);
                    }

                    return $this->customResponse->is200Response($response , $getList);

            }else{

                $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
                tp.nombre as nombre_tipo,
                users.user")
                    ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
                    ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
                    ->join("han_sg_permisos_empleados as empleado" , "empleado.id_permiso_trabajo" , "=" , "han_sg_permiso_trabajo.id_permiso")
                    ->where("empleado.id_user" , "=" , $idUsuario)
                    ->where("han_sg_permiso_trabajo.fecha_inicio" , "=" , $fecha)
                    ->where("han_sg_permiso_trabajo.estado" ,"=" , $estado)->get();

                    foreach($getList as $item)
                    {
                        $item->empleados = $this->findIntegrantes($item->id_permiso);
                        $cantidad = count($item->empleados);
                        $item->avance = $this->findEstadoFirmas($item->id_permiso , $cantidad , $idEmpresa);
                    }

                    return $this->customResponse->is200Response($response , $getList);
            }
        }else{

            if(empty($fecha))
            {
                $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
                tp.nombre as nombre_tipo,
                users.user")
                    ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
                    ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
                    ->where("han_sg_permiso_trabajo.id_empresa" , "=" , $idEmpresa)
                    ->where("han_sg_permiso_trabajo.estado" ,"=" , "1")->get();

                    foreach($getList as $item)
                    {
                        $item->empleados = $this->findIntegrantes($item->id_permiso);
                        $cantidad = count($item->empleados);
                        $item->avance = $this->findEstadoFirmas($item->id_permiso , $cantidad , $idEmpresa);
                    }

                    return $this->customResponse->is200Response($response , $getList);
            }else{

                $getList = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.* ,
                tp.nombre as nombre_tipo,
                users.user")
                    ->join("han_sg_tipos_trabajo as tp" , "tp.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
                    ->join("users" , "users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
                    ->where("han_sg_permiso_trabajo.id_empresa" , "=" , $idEmpresa)
                    ->where("han_sg_permiso_trabajo.fecha_inicio" , "=" , $fecha)
                    ->where("han_sg_permiso_trabajo.estado" ,"=" , $estado)->get();

                    foreach($getList as $item)
                    {
                        $item->empleados = $this->findIntegrantes($item->id_permiso);
                        $cantidad = count($item->empleados);
                        $item->avance = $this->findEstadoFirmas($item->id_permiso , $cantidad , $idEmpresa);
                    }

                    return $this->customResponse->is200Response($response , $getList);
            }

        }

        

        
    }

    private function findIntegrantes($permiso)
    {
        $empleado = $this->empleadoPermiso->selectRaw('id_user')->where("id_permiso_trabajo" , "=" , $permiso)->get();

        return $empleado;
    }

    /**
     * buscar firmas de empleado y firmasdetalle de jefes
     */
    private function findEstadoFirmas($idPermiso , $cantidadEmpleados , $idEmpresa)
    {
       
        $itemMedir = array("EPP" , "EPCC" , "Herramientas");
        
        $sumaGeneralidades = 0;
        
        foreach($itemMedir as $item)
        {   
            $generalidades = $this->getGeneralidadesCount( $item , $idPermiso);
            
            if($generalidades > 0)
            {
                $coeficiente = $generalidades / $cantidadEmpleados;

                 $sumaGeneralidades = $sumaGeneralidades + $coeficiente;
            }
        }
        //firmas
        $firmasEmpleado = $this->firmasEmpleado($idPermiso);

        $sumaGeneralidades  = $sumaGeneralidades + ($firmasEmpleado/ $cantidadEmpleados);

        //firmas Jefes
        $firmasJefes = $this->getFirmasJefes($idPermiso , $idEmpresa);

        $sumaGeneralidades  = $sumaGeneralidades + $firmaJefes;


        $reultado = $sumaGeneralidades / 5;
        
        
        return $resultado;

        
    }

    private function getFirmasJefes($idPermiso , $idEmpresa)
    {
        $validacion = 0;

            $count = $this->firmaEmpresa->where("id_empresa" , "=" , $idEmpresa)->count();

            if($count > 0)
            {
                    $jefes = $this->firmasJefes->where("id_permiso" , "=" , $idPermiso)->count();

                    if($jefes > 0)
                    {
                         $validacion = $jefes / $count;
                    }
            }

        return $validacion;
    }

    private function firmasEmpleado($idPermiso)
    {
        $count  = 0 ; 
        $firmas = $this->empleadoPermiso->where("id_permiso_trabajo" , "=" , $idPermiso)->get();

        foreach($firmas as $item)
        {
            if($item->firma != null)
            {
                $count++;
            }   
        }

        return $count;
    }
 

    private function getGeneralidadesCount($tipo , $idPermiso)
    {
        

            $gen  = 0; 

        $generalidades = $this->empleadoGeneralidades->selectRaw("count(han_sg_empleados_generalidades.active) as activo")
                ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
                ->where("han_sg_generalidades.tipo" , "=" , $tipo)
                ->where("han_sg_empleados_generalidades.permiso_id" , "=" , $idPermiso)
                ->where("han_sg_empleados_generalidades.active" , "=" , "Y")
                ->groupBy('han_sg_empleados_generalidades.empleado_id')
                ->get();

        foreach($generalidades as $item)
        {
               if($item->activo > 0)
               {
                    $gen++;
                }
        }
        
        return $gen;

         
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