<?php

namespace App\Controllers;

use App\Models\SGPermiso;
use App\Models\SGPermisoEmpleado;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use App\DomainDTO\TreeNode;
use App\Models\SGPermisoVehiculo;


class SGReportesController
{
    protected $permiso;

    protected $permiso_empleado;

    protected $customResponse;

    protected $validator;

    protected $vehiculo_permiso;

    public function __construct()
    {
        $this->permiso = new SGPermiso();

        $this->permiso_empleado = new SGPermisoEmpleado();

        $this->customResponse = new CustomResponse();

        $this->validator = new Validator();

        $this->vehiculo_permiso = new SGPermisoVehiculo();
    }

    /**select 
concat(han_sg_permiso_trabajo.prefijo ,' ' ,han_sg_permiso_trabajo.indicativo) as label, users.user as name,  
users.url_img as avatar from han_sg_permiso_trabajo
inner join users on users.id = han_sg_permiso_trabajo.id_usuario 
where han_sg_permiso_trabajo.fecha_inicio = "2023-06-24" */

    public function treeNode(Request $request , Response $response)
    {
        $fecha = CustomRequestHandler::getParam($request , "fecha");

        if(empty($fecha))
        {
            $fecha = date("Y-m-d");

            $list = $this->permiso->selectRaw("concat(han_sg_permiso_trabajo.prefijo ,' ' ,han_sg_permiso_trabajo.indicativo) as label, users.user as name,  
            users.url_img as avatar ,  han_sg_permiso_trabajo.id_permiso , han_sg_permiso_trabajo.id_usuario")
            ->join("users" , "users.id" , "=" ,"han_sg_permiso_trabajo.id_usuario")
            ->where("han_sg_permiso_trabajo.fecha_inicio"  , "=" , $fecha)
            ->where("han_sg_permiso_trabajo.estado" , "!=" , 0)
            ->get();
        }else{

            $list = $this->permiso->selectRaw("concat(han_sg_permiso_trabajo.prefijo ,' ' ,han_sg_permiso_trabajo.indicativo) as label, users.user as name,  
            users.url_img as avatar ,  han_sg_permiso_trabajo.id_permiso , han_sg_permiso_trabajo.id_usuario")
            ->join("users" , "users.id" , "=" ,"han_sg_permiso_trabajo.id_usuario")
            ->where("han_sg_permiso_trabajo.fecha_inicio"  , "=" , $fecha)
            ->where("han_sg_permiso_trabajo.estado" , "!=" , 0)
            ->get();
        }

        if($list->count() == 0)
        {   
            return $this->customResponse->is200Response($response , $list);
        } 
        $treeNode = new \stdClass();
        $treeNode->label = 'Permiso por dia';
        $treeNode->type = 'person';
        $treeNode->styleClass = 'p-person';
        $treeNode->expanded = true;
        $treeNode->data = array('name' => $fecha , 'avatar' => 'https://apps.internetinalambrico.com.co/Files/profile/default.jpg');

        $creadores =  array();

        foreach($list as $item)
        {
            $object = new \stdClass();

            $object->label = $item->label;
            $object->type = 'person';
            $object->styleClass = 'p-person';
            $object->expanded =  true;
            $temp = array('name' => substr($item->name , 0 ,10) , 'avatar' => 'https://apps.internetinalambrico.com.co/Files/profile/'.$item->avatar);
            $object->data = $temp;
            $object->children =  $this->getList($item->id_permiso , $item->id_usuario);
            
            array_push($creadores , $object);
        }

        $treeNode->children = $creadores;

        $this->customResponse->is200Response($response , $treeNode);
    }

    private function getList($idPermiso , $idUser)
    {
        /**select users.user from han_sg_permisos_empleados 
        inner join users on users.id = han_sg_permisos_empleados.id_user
        where han_sg_permisos_empleados.id_user not in (57) and han_sg_permisos_empleados.id_permiso_trabajo = 6 */
        $user = $this->permiso_empleado->selectRaw('users.user')
            ->join("users","users.id" , "=" ,  "han_sg_permisos_empleados.id_user")
            ->where("han_sg_permisos_empleados.id_permiso_trabajo" , "=" , $idPermiso)
            ->whereNotIn("han_sg_permisos_empleados.id_user" ,  [$idUser])
            ->get();

        
        $children = array();

        if($user->count() > 0)
        {
            foreach($user as $item)
                {
                    $child = new \stdClass();
                    $child->label = substr($item->user , 0 , 10);
                    $child->styleClass = 'department-cfo';

                    array_push($children , $child);

                }
        }

        return $children;


    }

    /**
     * 
     */
    public function reporte_vehiculo_kilometraje(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "initial" => v::notEmpty(),
            "finally" => v::notEmpty(),
            "vehiculo_id" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		} 
         
        $getVehiculo = $this->vehiculo_permiso->selectRaw("han_sg_permisos_vehiculos.kilometro , DATE_FORMAT(created_at , '%m%d') as fecha")
                                ->whereBetween('created_at', [CustomRequestHandler::getParam($request , "initial"), CustomRequestHandler::getParam($request , "finally")])
                                ->where("vehiculo_id" , "=" , CustomRequestHandler::getParam($request , "vehiculo_id"))
                                ->get();
        
        $x = array();
        $y = array();
        foreach($getVehiculo as $item)
        {
            array_push($x , $item->fecha);
            array_push($y , $item->kilometro);
        }

        $xx = array();
        $yy = array();
        $temp = 0;
        foreach($getVehiculo as $link)
        {
            if($link->kilometro > 0)
            {
                if($temp == 0)
                {
                    $temp = $link->kilometro;
                    
                }else{

                    array_push($yy , $link->kilometro - $temp);
                    array_push($xx , $link->fecha);

                    $temp = $link->kilometro;
                }


            }
        }


        $responseMessage  = array("acumulado" => array("meses" => $x ,"kilometros" => $y ) , "diadia" => array("meses" => $xx , "kilometros" => $yy));

        $this->customResponse->is200Response($response , $responseMessage);


    }

    /**
     * SEARCH POR PERMISO AND EMPRESA 
     */
    public function reporte_permiso_vehicular(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "fecha" => v::notEmpty(),
            "id_empresa" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

      /*  SELECT 
            hspt.id_permiso ,
            hspt.prefijo ,
            hspt.indicativo ,
            hspt.lugar_de_trabajo ,
            hspv.kilometro ,
            u.user ,
            hsv.vehiculo_placa ,
            hsv.vehiculo_cilindraje ,
            hsv.vehiculo_modelo 
            FROM han_sg_permiso_trabajo hspt 
            INNER JOIN han_sg_permisos_vehiculos hspv ON hspv.permiso_id = hspt.id_permiso
            INNER JOIN han_sg_vehiculos hsv ON hsv.vehiculo_id = hspv.vehiculo_id 
            INNER JOIN users u ON u.id = hspv.conductor_id 
            WHERE hspt.fecha_inicio = "2024-07-31"*/
        $getListVehiculos = $this->permiso->selectRaw("han_sg_permiso_trabajo.id_permiso ,
        han_sg_permiso_trabajo.prefijo ,
        han_sg_permiso_trabajo.indicativo ,
        han_sg_permiso_trabajo.lugar_de_trabajo ,
        han_sg_permisos_vehiculos.kilometro ,
        users.user ,
        han_sg_vehiculos.vehiculo_placa ,
        han_sg_vehiculos.vehiculo_cilindraje ,
        han_sg_vehiculos.vehiculo_modelo ")
        ->join("han_sg_permisos_vehiculos" ,"han_sg_permisos_vehiculos.permiso_id" , "=" , "han_sg_permiso_trabajo.id_permiso" )
        ->join("han_sg_vehiculos" , "han_sg_vehiculos.vehiculo_id" , "=" , "han_sg_permisos_vehiculos.vehiculo_id")
        ->join("users" , "users.id" , "=" , "han_sg_permisos_vehiculos.conductor_id")
        ->where("han_sg_permiso_trabajo.fecha_inicio" , "=" , CustomRequestHandler::getParam($request , "fecha"))
        ->where("han_sg_permiso_trabajo.id_empresa" , "=" , CustomRequestHandler::getParam($request , "id_empresa"))
        ->get();  
         
        
        $this->customResponse->is200Response($response , $getListVehiculos);
    }
}