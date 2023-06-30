<?php

namespace App\Controllers;

use App\Models\SGDetalleFirmas;
use App\Models\SGFirma;
use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGPermiso;
use App\Models\SGPermisoVehiculo;
use App\Models\SGVehiculosGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGDetalleFirmasController
{
    protected $detalle;

    protected $validator;

    protected $customResponse;

    protected $firma;

    protected $permisoEmpleado;

    protected $permiso;

    protected $generalidadesEmpleado;

    protected $vehiculos;

    protected $vehiculosInspeccion;

    public function __construct()
    {
        $this->detalle = new SGDetalleFirmas();

        $this->validator = new Validator();

        $this->customResponse = new CustomResponse();

        $this->firma = new SGFirma();

        $this->permisoEmpleado = new SGPermisoEmpleado();

        $this->permiso = new SGPermiso();

        $this->generalidadesEmpleado = new SGEmpleadoGeneralidades();

        $this->vehiculos = new SGPermisoVehiculo();

        $this->vehiculosInspeccion = new SGVehiculosGeneralidades();
    }

    public function listByIdPermiso(Request $request , Response $response , $id)
    {
        /**SELECT dfr.id, dfr.url_firma , dfr.updated_at, users.id id_user, users.user , users.url_img  FROM  han_sg_detalle_firma dfr
                inner join `han_sg_firmas` fr on fr.id = dfr.id_firma
                inner join `users` on users.id = fr.id_user 
                where dfr.id_permiso = 3 ; */
            $get = $this->detalle->selectRaw("han_sg_detalle_firma.id , han_sg_detalle_firma.url_firma , han_sg_detalle_firma.updated_at,
                                                users.id id_user , users.user , users.url_img")
                                                ->join("han_sg_firmas" , "han_sg_firmas.id" , "=" , "han_sg_detalle_firma.id_firma")
                                                ->join("users" , "users.id" , "=" , "han_sg_firmas.id_user")
                                                ->where(["han_sg_detalle_firma.id_permiso" => $id])
                                                ->get();

            $this->customResponse->is200Response($response , $get);
    }

    public function create(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),            
            "id_empresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        //buscar si tiene firmas
        $getFirmas = $this->firma->where("id_empresa" , "=" , CustomRequestHandler::getParam($request , "id_empresa"))->get();
        if($getFirmas->count() == 0)
        {
            $responseMessage = "No tiene personal habilitado para firmar";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        try{

            foreach($getFirmas as $item)
            {
                $this->detalle->create([
                    "id_firma" => $item->id,
                    "id_permiso" => CustomRequestHandler::getParam($request , "id_permiso")
                ]);
            }
        $responseMessage = "creado";

        $this->customResponse->is200Response($response , $responseMessage);

        }catch(Exception $e)
        {
                $responseMessage = $e->getMessage();

                $this->customResponse->is400Response($response , $responseMessage);
        }

    }

    /**
     * ENDPOINT POST
     */

    public function firmarPermisoJefes(Request $request , Response $response)
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

            $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

            $id_user = CustomRequestHandler::getParam($request , "id_user");

            //consultamos si el empleado ya firmo el permiso
            
            //consultamos informacion de todos los datos
            /**
             * permiso de trabajo puede cambiar estado cerrado
             * han_sg_permiso_empleados  => han_sg_empleado_generalidades
             * han_sg_permiso_vehiculos => han_sg_vehiculo_generalidades
             * han_sg_permiso_peligros =>
             */
            $permiso = new \stdClass();
            $permiso->empleados = $this->getEmpleados($id_permiso);
             
            if($permiso->empleados->count() > 0)
            {
                foreach($permiso->empleados as $item)
                {
                    if(empty($item->firma))
                    {
                        return $this->customResponse->is400Response($response , "existen empleados que no han firmado");
                    }

                    $item->generalidades = $this->getGeneralidadesEmpleados($id_user , $id_permiso);
                }
            }

            $permiso->vehiculos = $this->getVehiculos($id_permiso);

            if($permiso->vehiculos->count() > 0)
            {
                foreach($permiso->vehiculos as $item)
                {
                    $item->inspeccion = $this->getVehiculoGeneralidades($item->permiso_vehiculo_id);

                }
            }

            $key_private_k1 = $this->key_file_exist($id_user);
            if(!$key_private_k1)
            {

                $responseMessage = "no se encontro el archivo .pem ".__DIR__;

                return $this->customResponse->is404Response($response , $responseMessage);
            }

            
            
            $id_detalle_firma = $this->detalle->selectRaw("han_sg_detalle_firma.id")
                                    ->join("han_sg_firmas" , "han_sg_firmas.id" ,"=" , "han_sg_detalle_firma.id_firma")
                                    ->where("han_sg_firmas.id_user" , "=" , $id_user)
                                    ->where("han_sg_detalle_firma.id_permiso" , "=" , $id_permiso)
                                    ->get();

            if($id_detalle_firma->count() == 0)
            {   
                $responseMessage = "No existe firmas para generar";

                return $this->customResponse->is400Response($response , $responseMessage);
            }

            $encrypted = $this->encriptation($permiso);

            $private_key_pem = fopen($key_private_k1 , "r");

            $cert = fread($private_key_pem , 8192);

            fclose($private_key_pem);

            openssl_sign($encrypted , $firma , $cert , OPENSSL_ALGO_SHA256);

            //CREANDO RUTA DE FIRMA
            $pathFirma = "/home/internet/public_html/apps/Files/usuarios/firmaPermisos/".$id_user;
            //$pathFirma = "/home/programador/Documentos/10";
            if (!is_dir($pathFirma)) 
            {
                mkdir($pathFirma, 0777, true);
            }
            //ruta para nombre de firma
            $pathFirmaData = "/home/internet/public_html/apps/Files/usuarios/firmaPermisos/".$id_user."/".date('Y-m-d H:s:i')."_".$id_permiso."_firma.dat";
            //$pathFirmaData = "/home/programador/Documentos/10/".date('Y-m-d H:s:i')."_firma.dat";
            //almacenando en ruta
            file_put_contents($pathFirmaData , $firma);
            $unid = uniqid();
            $contenidoUrl = "/home/internet/public_html/apps/Files/usuarios/firmaPermisos/".$id_user."/".$unid."_".$id_permiso.".txt";
            //$contenidoUrl = "/home/programador/Documentos/10/".$unid."contenido_.txt";

            file_put_contents($contenidoUrl , $encrypted);

            foreach($id_detalle_firma as $item)
            {
                $this->detalle->where("id" , "=" , $item->id)->update([
                    "url_firma" => $pathFirmaData,
                    "contenido" => $contenidoUrl,
                    "fecha_firma" => date('Y-m-d')
                ]);
            }

            $responseMessage = "firmado";

            $this->customResponse->is200Response($response , $responseMessage );
    }

   

    private function encriptation($contenido)
    {
        $serializeData = serialize($contenido);

        $key = "a075b77ca446d504377e42bc23cae70a";

        $encrypted = openssl_encrypt($serializeData , 'AES-256-CBC' , $key , 0 , '1234567890123456');


        return $encrypted;
    }

    private function key_file_exist($id_user)
    {
            $file_key = '/home/internet/public_html/apps/Files/usuarios/frmEOL/'.$id_user.'/private_key1.pem';

            //$file_key = '/home/programador/Documentos/10/private_key1.pem';

            if(!file_exists($file_key))
            {
                return false;
            }

            return $file_key;
    }

    //armarJson
    private function getEmpleados($idPermiso)
    {
        $empleado = $this->permisoEmpleado
                            ->selectRaw("han_sg_permisos_empleados.id_permisos_empleado, han_sg_permisos_empleados.id_user,
                            han_sg_permisos_empleados.firma, han_sg_permisos_empleados.id_empresa , han_sg_permisos_empleados.updated_at")
                            ->where("han_sg_permisos_empleados.id_permiso_trabajo" , "=" , $idPermiso)->get();
        
                            return $empleado;
    }

    private function getGeneralidadesEmpleados($idEmpleado , $idPermiso){

        $generalidades = $this->generalidadesEmpleado->selectRaw("han_sg_empleados_generalidades.active, han_sg_empleados_generalidades.inspeccion,
        han_sg_generalidades.nombre, han_sg_generalidades.tipo")
            ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
            ->where("han_sg_empleados_generalidades.permiso_id" , "=" ,$idPermiso)
            ->where("han_sg_empleados_generalidades.empleado_id" , "=" , $idEmpleado)
            ->where("han_sg_empleados_generalidades.active" , "=" , "Y")
            ->get();
        return $generalidades;
    }

    private function getVehiculos($idPermiso)
    {
        $getVehiculos = $this->vehiculos->selectRaw("
        han_sg_permisos_vehiculos.permiso_vehiculo_id , 
        han_sg_vehiculos.vehiculo_placa , 
        han_sg_vehiculos.vehiculo_nombre_tarjeta,
        users.user as conductor")
        ->join("han_sg_vehiculos" ,"han_sg_vehiculos.vehiculo_id" , "=" , "han_sg_permisos_vehiculos.vehiculo_id")
        ->join("users" , "users.id" , "=","han_sg_permisos_vehiculos.conductor_id")
        ->where("han_sg_permisos_vehiculos.permiso_id" , "=" , $idPermiso)
        ->get();

        return $getVehiculos;
    }

    private function getVehiculoGeneralidades($idVehiculoGeneralidad)
    {
            $generalidades = $this->vehiculosInspeccion->selectRaw(
                "han_sg_generalidades.nombre, han_sg_generalidades.item, han_sg_vehiculos_generalidades.inspeccion"
            )
                ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_vehiculos_generalidades.generalidades_id")
                ->where("han_sg_vehiculos_generalidades.permiso_vehiculo_id" , "=" , $idVehiculoGeneralidad)
                ->get();

            return $generalidades;
    }
}