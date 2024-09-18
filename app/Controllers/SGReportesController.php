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



class SGReportesController
{
    protected $permiso;

    protected $permiso_empleado;

    protected $customResponse;

    protected $validator;

    public function __construct()
    {
        $this->permiso = new SGPermiso();

        $this->permiso_empleado = new SGPermisoEmpleado();

        $this->customResponse = new CustomResponse();

        $this->validator = new Validator();
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
        $treeNode->data = array('name' => $fecha , 'avatar' => 'https://permisos.comunicamosmas.com/Files/profile/default.jpg');

        $creadores =  array();

        foreach($list as $item)
        {
            $object = new \stdClass();

            $object->label = $item->label;
            $object->type = 'person';
            $object->styleClass = 'p-person';
            $object->expanded =  true;
            $temp = array('name' => substr($item->name , 0 ,10) , 'avatar' => 'https://permisos.comunicamosmas.com/Files/profile/'.$item->avatar);
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
}