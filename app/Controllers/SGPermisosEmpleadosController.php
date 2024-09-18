<?php

namespace App\Controllers;

use App\Controllers\WsSendMessageController;
use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGPermiso;
use App\Models\SGPermisosPeligros;
use App\Models\SGControles;
use App\Models\SGPermisoVehiculo;
use App\Models\SGVehiculosGeneralidades;
use App\Models\SGObservaciones;
use App\Models\DatosPersonales;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPermisosEmpleadosController
{
    protected $sgObservaciones;

    protected $customResponse;

    protected $sgPermisoEmpleado;

    protected $validator;

    protected $sgEmpleadoGeneralidades;

    protected $sgPermiso;

    protected $peligros;

    protected $controles;

    protected $permisoVehiculo;

    protected $vehiculoGeneralidades;

    protected $wsSendMessage;

    protected $datosPersonales;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgPermisoEmpleado = new SGPermisoEmpleado();

        $this->validator = new Validator();

        $this->sgEmpleadoGeneralidades = new SGEmpleadoGeneralidades();

        $this->sgPermiso = new SGPermiso();

        $this->peligros = new SGPermisosPeligros();

        $this->controles = new SGControles();

        $this->permisoVehiculo = new SGPermisoVehiculo();

        $this->vehiculoGeneralidades = new SGVehiculosGeneralidades();

        $this->sgObservaciones = new SGObservaciones();

        $this->wsSendMessage = new WsSendMessageController();

        $this->datosPersonales = new DatosPersonales();
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
        $id = CustomRequestHandler::getParam($request , "id_user");

        $datos_personales =  $this->datosPersonales->where("id_user" , "=" , $id)->get();
        $whatsapp = "";
        foreach($datos_personales as $item)
        {
            $whatsapp = $item->celular;
        }
        if(!empty($whatsapp))
        {
            $msm = "Ha sigo agregado a un permiso de trabajo ingrese al portal web y diligencielo https://permisos.comunicamoasmas.com/init";
            $this->wsSendMessage->send_text($whatsapp , $msm);
        }

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
        $getFindByIdpermiso = $this->sgPermisoEmpleado->selectRaw("han_sg_permisos_empleados.id_user ,han_sg_permisos_empleados.id_permisos_empleado, users.user , users.email, han_sg_permisos_empleados.firma")
                                                        ->join("users" , "users.id" , "=" , "han_sg_permisos_empleados.id_user")
                                                        ->where(["id_permiso_trabajo" => $id])
                                                        ->get();

        $this->customResponse->is200Response($response , $getFindByIdpermiso);
    }

    public function deleteById(Request $request , Response $response , $id)
    {
        //consultar informacion del permiso
        $permisoEmpleado = $this->sgPermisoEmpleado->where(["id_permisos_empleado" => $id])->get();

        if($permisoEmpleado->count() > 0)
        {
        $id_usuario = $permisoEmpleado[0]->id_user;
        $id_permiso = $permisoEmpleado[0]->id_permiso_trabajo;
        //echo $id_permiso." ".$id_usuario;
        //buscar si esta como conductor
        $conductor = $this->permisoVehiculo->where("permiso_id" , "=" , $id_permiso)->where("conductor_id" , "=" , $id_usuario)->count();

        if($conductor > 0)
        {
            $responseMessage = "Empleado se encuentra como conductor";

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        //eliminamos el usuario 
        
        //han_sg_empleados_generalidades consultamos 
        $findGeneralidads = $this->sgEmpleadoGeneralidades->where("empleado_id", "=", $id_usuario)->where("permiso_id" , "=" , $id_permiso)->get();
        if($findGeneralidads->count() > 0)
        {
            //proceder a eliminar registros
            //echo $findGeneralidads;
            //$this->sgEmpleadoGeneralidades->whereIn("empleado_generalidades_id" , [$findGeneralidads])->delete();
            foreach ($findGeneralidads as $key)
            {
                $this->sgEmpleadoGeneralidades->where("empleado_generalidades_id" , "=" , $key->empleado_generalidades_id)->delete();
            }
        }

        //han_sg_permisos_observaciones 
        $findObservaciones = $this->sgObservaciones->where("id_usuario" , "=" , $id_usuario)->where("id_permiso" , "=" , $id_permiso)->get();

        if($findObservaciones->count() > 0 )
        { 
            //$this->sgObservaciones->whereIn("id_observacion" , [$findObservaciones])->delete();
            foreach($findObservaciones as $item)
            {
                $this->sgObservaciones->where("id_observacion" , "=" , $item->id_observacion)->delete();
            }
        }

        $this->sgPermisoEmpleado->where(["id_permisos_empleado" => $id])->delete();

        $responseMessage = "Eliminado";

        $this->customResponse->is200Response($response  , $responseMessage); 
        }else{

            $this->customResponse->is400Response($response  , "no existe el id"); 
        }
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

     

    /**
     * informacion del permiso completo*/
    public function permisoFinal(Request $request , Response $response , $id)
    {
        $info = $this->datosPermisoParaFirma($id["id"]);

        return $this->customResponse->is200Response($response , $info);
    }

    /**
     * FIRMAR PERMISOS GENERAL SST SUPERVISOR*/
     function datosPermisoParaFirma($id_permiso)
    {
         
                  //consultamos informacion del  permiso
                    /**SELECT 
            han_sg_permiso_trabajo.id_permiso,
            han_sg_permiso_trabajo.fecha_inicio,
            han_sg_permiso_trabajo.hora_inicio,
            han_sg_permiso_trabajo.lugar_de_trabajo,
            han_sg_permiso_trabajo.estado,
            han_sg_permiso_trabajo.prefijo,
            han_sg_permiso_trabajo.indicativo,
            users.user,
            han_sg_empresa.razon_social,
            han_sg_tipos_trabajo.nombre as tipo_trabajo

             FROM han_sg_permiso_trabajo
             inner join users on users.id = han_sg_permiso_trabajo.id_usuario
             inner join han_sg_empresa on han_sg_empresa.id_empresa = han_sg_permiso_trabajo.id_empresa
             inner join han_sg_tipos_trabajo on han_sg_tipos_trabajo.id_tipo = han_sg_permiso_trabajo.id_permiso_trabajo*/
          

             $permiso = $this->sgPermiso->selectRaw("han_sg_permiso_trabajo.id_permiso,
            han_sg_permiso_trabajo.fecha_inicio,
            han_sg_permiso_trabajo.hora_inicio,
            han_sg_permiso_trabajo.lugar_de_trabajo,
            han_sg_permiso_trabajo.estado,
            han_sg_permiso_trabajo.prefijo,
            han_sg_permiso_trabajo.indicativo,
            users.user,
            han_sg_empresa.razon_social,
            han_sg_tipos_trabajo.nombre as tipo_trabajo")
             ->join("users" ,"users.id" , "=" , "han_sg_permiso_trabajo.id_usuario")
             ->join("han_sg_empresa",  "han_sg_empresa.id_empresa",  "=" ,  "han_sg_permiso_trabajo.id_empresa")
             ->join("han_sg_tipos_trabajo" , "han_sg_tipos_trabajo.id_tipo" , "="  , "han_sg_permiso_trabajo.id_permiso_trabajo")
             ->where("han_sg_permiso_trabajo.id_permiso", "=", $id_permiso)
             ->get();
             foreach($permiso as $per)
             {
                $per->empleados = $this->infoEmpleados($id_permiso);
                $per->peligros =  $this->getPeligros($id_permiso);
                $per->vehiculos = $this->getVehiculos($id_permiso);
             }

            //$this->customResponse->is200Response($response , $permiso);

             return $permiso;
    }

    /**
     * INFORMACION DE VEHICULOS*/
    function getVehiculos($idPermiso)
    {
        $get = $this->permisoVehiculo->selectRaw("
                    han_sg_permisos_vehiculos.permiso_vehiculo_id,
                    han_sg_permisos_vehiculos.observaciones,
                    han_sg_permisos_vehiculos.vehiculo_id,
                    han_marca.marca_nombre,
                    han_sg_vehiculos.vehiculo_nombre_tarjeta,
                    han_sg_vehiculos.vehiculo_color,
                    han_sg_vehiculos.vehiculo_placa,
                    han_sg_vehiculos.vehiculo_cilindraje,
                    han_sg_vehiculos.vehiculo_modelo

            ")
                    ->join("users" , "users.id" , "=" , "han_sg_permisos_vehiculos.conductor_id")
                    ->join("han_sg_vehiculos" , "han_sg_vehiculos.vehiculo_id" , "=" , "han_sg_permisos_vehiculos.vehiculo_id")
                    ->join("han_marca" , "han_marca.id_marca" , "=" ,  "han_sg_vehiculos.id_marca")
                    ->where("han_sg_permisos_vehiculos.permiso_id" , "=" , $idPermiso)
                    ->get();

        foreach($get as $item)
        {
            $item->inspeccion = $this->getVehiculoInspeccion($item->permiso_vehiculo_id);
        }

        return $get;
    }

    /**
     * INSPECCION DEL VEHICULO*/
    function getVehiculoInspeccion($idPermisoVehiculo)
    {
        $inspeccion = $this->vehiculoGeneralidades->selectRaw("
                                han_sg_vehiculos_generalidades.vehiculo_generalidades_id,
                                han_sg_generalidades.nombre,
                                han_sg_generalidades.tipo,
                                han_sg_generalidades.item,
                                han_sg_vehiculos_generalidades.inspeccion
                            ")
                            ->join("han_sg_generalidades" ,"han_sg_generalidades.id_generalidades" , "=" , "han_sg_vehiculos_generalidades.generalidades_id")
                            ->where("han_sg_vehiculos_generalidades.permiso_vehiculo_id", "=" , $idPermisoVehiculo)
                            ->whereNotNull("han_sg_vehiculos_generalidades.inspeccion")
                            ->get();
        return $inspeccion;
    }

    /**
     * INFORMACION DE EMPLEADOS
     * */
      function infoEmpleados($idPermiso)
    {
        /**
         * SELECT 
         * han_sg_permisos_empleados.firma
         * FROM han_sg_permisos_empleados
         * INNER JOIN users */
        $getInfoEmpleado = $this->sgPermisoEmpleado->selectRaw("
                                    han_sg_permisos_empleados.id_permisos_empleado,
                                    han_sg_permisos_empleados.firma,
                                    users.user , 
                                    han_sg_permisos_empleados.id_user,
                                    datos_personales.documento , 
                                    datos_personales.cargo , 
                                    han_sg_empresa.razon_social")
                                ->join("users" , "users.id" , "=" , "han_sg_permisos_empleados.id_user")
                                ->join("han_sg_empresa" , "han_sg_empresa.id_empresa" , "=" , "han_sg_permisos_empleados.id_empresa")
                                ->join("datos_personales" , "datos_personales.id_user" , "=" , "han_sg_permisos_empleados.id_user")
                                ->where("han_sg_permisos_empleados.id_permiso_trabajo" , "=" , $idPermiso)
                                ->get();
                foreach($getInfoEmpleado as $item)
                {
                    $item->preoperacional = $this->getGeneralidadesEmpleados($item->id_user , $idPermiso);
                }
        return $getInfoEmpleado;
    }
    /**
     * buscar generalidades por empleados*/
      function getGeneralidadesEmpleados($idUser , $permiso)
    {
        $getGeneralidades = $this->sgEmpleadoGeneralidades->selectRaw("
                            han_sg_generalidades.tipo,
                            han_sg_generalidades.nombre,
                            han_sg_empleados_generalidades.active,
                            han_sg_empleados_generalidades.inspeccion")
                        ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
                        ->where("han_sg_empleados_generalidades.empleado_id" , "=" , $idUser)
                        ->where("han_sg_empleados_generalidades.permiso_id" , "=" , $permiso)
                        ->where("han_sg_empleados_generalidades.active" , "=" , "Y")
                        ->get();

        return $getGeneralidades;
    }

    /**
     * peligros del permiso*/
    function getPeligros($idPermiso)
    {
            $getPeligro = $this->peligros->selectRaw("han_sg_permisos_peligros.permiso_peligro_id,
                                                han_sg_permisos_peligros.peligro_id,
                                                han_sg_peligros.nombre,
                                                han_sg_peligros.consecuencias")
                                            ->join("han_sg_peligros" , "han_sg_peligros.id_peligro" , "=" , "han_sg_permisos_peligros.peligro_id")
                                            ->where("han_sg_permisos_peligros.permiso_id" , "=" , $idPermiso)
                                            ->get();
            foreach($getPeligro as $item)
            {
                $item->controles = $this->getControles($item->peligro_id);
            }

            return $getPeligro;
    }

    /**
     * CONSULTAR LAS CONTROLES DEL PELIGRO*/
    function getControles($idPeligro)
    {
        $getControl = $this->controles->selectRaw("han_sg_controles.id_control , han_sg_controles.nombre")
                                ->where("han_sg_controles.id_peligro" , "=" , $idPeligro)
                                ->get();

        return $getControl;
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
        $path = '/home/wwcomu/permisos.comunicamosmas.com/Files/usuarios/frmEOL/'.$carpetaUser.'/private_key1.pem';

        $private_key_pem = fopen($path , "r");

        $cert = fread($private_key_pem, 8192);

        fclose($private_key_pem);

        openssl_sign($getDataFirma, $firma, $cert, OPENSSL_ALGO_SHA256);

        //crear directorio donde va a estar la firma
        $pathFirma = '/home/wwcomu/permisos.comunicamosmas.com/Files/usuarios/firmaPermisos/'.$carpetaUser;
        
        if (!is_dir($pathFirma)) 
        {
            mkdir($pathFirma, 0777, true);
        }
        $permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $pathFirmaData = '/home/wwcomu/permisos.comunicamosmas.com/Files/usuarios/firmaPermisos/'.$carpetaUser.'/'.date("Y-m-d H:s:i").'_'.$permiso.'_firma.dat';

        file_put_contents($pathFirmaData , $firma);

        $this->sgPermisoEmpleado->where("id_permisos_empleado" , "=" , $id_permiso_empleado)->update(["firma" => $pathFirmaData]);

        $responseMessage = "firma creada con éxito";

        $this->customResponse->is200Response($response , $responseMessage);
    }

    //validamos si existe el archivo pem
    public function validarExistFile($carpeta , $archivo)
    {
        $nombre_fichero = '/home/wwcomu/permisos.comunicamosmas.com/Files/usuarios/frmEOL/'.$carpeta.'/'.$archivo;

        if (file_exists($nombre_fichero)) {
                return true;
        } else {
                return false;
        }
    }

    /**
     * ENDPOINT
     * BUSCAR EMPLEADOS CON NUMERO DE CEDULA Y DOCUMENTO
     * GET FINDBYIDPERMISO*/
    public function getListEmpleadosWithDatosPersonales(Request $request , Response $response , $id)
    {
        try{

        $getlist = $this->sgPermisoEmpleado->selectRaw("users.user, datos_personales.documento, datos_personales.tipo_documento , datos_personales.cargo")
        ->leftjoin("datos_personales","datos_personales.id_user", "=", "han_sg_permisos_empleados.id_user")
        ->leftjoin("users" , "users.id" , "=" , "datos_personales.id_user")
        ->where(["han_sg_permisos_empleados.id_permiso_trabajo" => $id])
        ->get();

        $count = $this->sgPermisoEmpleado->where(["han_sg_permisos_empleados.id_permiso_trabajo" => $id])->count();

        $responseArray = array("empleados" => $getlist , "cuenta" => $count);

        $this->customResponse->is200Response($response , $responseArray);

        }catch(Exception $e)
        {
            $this->customResponse->is400Response($response , $e->getMessage());
        }
    }

    /**
     * POST
     * BUSCAR PERMISO_EMPLEADO*/
    public function firmaFindByIdPermisoAndIdUser(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),
            "id_user"   => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        try{

            $get = $this->sgPermisoEmpleado->where(["id_permiso_trabajo" => CustomRequestHandler::getParam($request , "id_permiso")])
                                            ->where(["id_user" => CustomRequestHandler::getParam($request , "id_user")])
                                            ->get();

            $this->customResponse->is200Response($response , $get);
        }catch(Exception $e)
        {
            $this->customResponse->is400Response($response , $e->getMessage());
        }
    }

    /**
     * get empleados list */

     public function listEmpleado(Request $request , Response $response , $id)
     {
        $getList = $this->sgPermisoEmpleado
            ->selectRaw("users.id , users.user, users.url_img")
            ->join("users" , "users.id" , "=" , "han_sg_permisos_empleados.id_user")
            ->where(["han_sg_permisos_empleados.id_permiso_trabajo"=>$id])
            ->get();

        $this->customResponse->is200Response($response , $getList);
    
    
    }

    /**información para firmar x empleado
     * en caso que sea el que creo el permiso, debe agregar los vehiculos
    */
    public function inforParaFirmaEmpleado(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_user" => v::notEmpty(),
            "id_permiso" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $id_user = CustomRequestHandler::getParam($request , "id_user");
        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $empleado = $this->sgPermisoEmpleado->selectRaw('users.id , users.user,han_sg_permisos_empleados.firma , han_sg_permisos_empleados.id_permiso_trabajo')
        ->join('users' , 'users.id', '=' ,'han_sg_permisos_empleados.id_user')
        ->where('han_sg_permisos_empleados.id_user' , '=' , $id_user)
        ->where('han_sg_permisos_empleados.id_permiso_trabajo' , "=" , $id_permiso )
        ->get();

        if($empleado->count() > 0)
        {
            foreach($empleado as $item)
            {
                $item->generalidades = $this->getGeneralidadesEmpleados($id_user , $id_permiso); 

            }
        }

        $this->customResponse->is200Response($response , $empleado);

    }

    /**generlaidades con inspeccion de vehiculos y peligros */
    public function infoParaFirmarCreadorEmpleado(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_user" => v::notEmpty(),
            "id_permiso" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $id_user = CustomRequestHandler::getParam($request , "id_user");
        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $empleado = $this->sgPermisoEmpleado->selectRaw('users.id , users.user,han_sg_permisos_empleados.firma , han_sg_permisos_empleados.id_permiso_trabajo')
                        ->join('users' , 'users.id', '=' ,'han_sg_permisos_empleados.id_user')
                        ->where('han_sg_permisos_empleados.id_user' , '=' , $id_user)
                        ->where('han_sg_permisos_empleados.id_permiso_trabajo' , "=" , $id_permiso )
                        ->get();

        if($empleado->count() > 0)
        {
            foreach($empleado as $item)
            {
                $item->generalidades = $this->getGeneralidadesEmpleados($id_user , $id_permiso);
                $item->peligros  = $this->getPeligros($id_permiso);
                $item->vehiculos = $this->getVehiculos($id_permiso);
            }
        }

        $this->customResponse->is200Response($response , $empleado);
    }

     


}