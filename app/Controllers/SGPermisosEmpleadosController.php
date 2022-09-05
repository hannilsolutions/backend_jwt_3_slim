<?php

namespace App\Controllers;

use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
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

    protected $sgEmpleadoGeneralidades;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermisoEmpleado = new SGPermisoEmpleado();

        $this->validator = new Validator();

        $this->sgEmpleadoGeneralidades = new SGEmpleadoGeneralidades();

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
            han_sg_permisos_empleados.firma,
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
    /*
    *  ENDPOINT POST
    SELECT * from han_sg_empleados_generalidades WHERE han_sg_empleados_generalidades.permiso_id = 34 and han_sg_empleados_generalidades.empleado_id = 10 
    */

    public function firmarEmpleado(Request $request , Response $response )
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),
            "id_empleado" => v::notEmpty()
           // "id_permisos_empleado" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {

            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        #consultamos el id_permiso_empleado de la tabla 
        $getIdPermisoEmpleado = $this->sgPermisoEmpleado->selectRaw("id_permisos_empleado")->where("id_permiso_trabajo" ,"=" ,CustomRequestHandler::getParam($request , "id_permiso"))->where("id_user" , "=" , CustomRequestHandler::getParam($request , "id_empleado"))->get();
        
        #reconrremos la info
        foreach($getIdPermisoEmpleado as $item)
        {
            $id_permiso_empleado = $item->id_permisos_empleado;
        }

        //consultar informacion de empleado_generalidades para firmar
        $getDataFirma = $this->sgEmpleadoGeneralidades
                            ->where("empleado_id", "=", CustomRequestHandler::getParam($request , "id_empleado"))
                            ->where("permiso_id" , "=" , CustomRequestHandler::getParam($request , "id_permiso"))
                            ->get();
        #cargar firma
        $carpetaUser = CustomRequestHandler::getParam($request , "id_empleado");

        
        if(!$this->validarExistFile($carpetaUser , "private_key1.pem"))
        {
            $responseMessage = "no existe archivo pem";

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $path = '/home/internet/public_html/apps/Files/usuarios/frmEOL/'.$carpetaUser.'/private_key1.pem';

        $private_key_pem = fopen($path , "r");

        $cert = fread($private_key_pem, 8192);

        fclose($private_key_pem);

        openssl_sign($getDataFirma, $firma, $cert, OPENSSL_ALGO_SHA256);

        //crear directorio donde va a estar la firma
        $pathFirma = '/home/internet/public_html/apps/Files/usuarios/firmaPermisos/'.$carpetaUser;
        
        if (!is_dir($pathFirma)) 
        {
            mkdir($pathFirma, 0777, true);
        }
        $permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $pathFirmaData = '/home/internet/public_html/apps/Files/usuarios/firmaPermisos/'.$carpetaUser.'/'.date("Y-m-d H:s:i").'_'.$permiso.'_firma.dat';

        file_put_contents($pathFirmaData , $firma);

        $this->sgPermisoEmpleado->where("id_permisos_empleado" , "=" , $id_permiso_empleado)->update(["firma" => $pathFirmaData]);

        $responseMessage = "firma creada con éxito";

        $this->customResponse->is200Response($response , $responseMessage);
    }

    //validamos si existe el archivo pem
    public function validarExistFile($carpeta , $archivo)
    {
        $nombre_fichero = '/home/internet/public_html/apps/Files/usuarios/frmEOL/'.$carpeta.'/'.$archivo;

        if (file_exists($nombre_fichero)) {
                return true;
        } else {
                return false;
        }
    }


}