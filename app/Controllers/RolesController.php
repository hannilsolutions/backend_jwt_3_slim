<?php

namespace App\Controllers;

use App\Models\Roles;
use App\Models\Han_Relations;
use App\Models\Han_Gruop;
use App\Models\Han_Views;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class RolesController
{

    protected  $customResponse;

    protected  $roles;

    protected  $validator;

    protected $relations;

    protected $group;

    protected $view;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->roles = new Roles();

         $this->validator = new Validator();

         $this->relations = new Han_Relations();

         $this->group = new Han_Gruop();

         $this->view = new Han_Views();
    }

/*
*GET list roles
*/
     public function findByRole(Request $request , Response $response)
     {
         $roles = $this->roles->get();

         $responseMenssage = ["roles" => $roles];

         $this->customResponse->is200Response($response ,  $responseMenssage);
     }

/*
*GET buscar roles x rol
*/
/*

select 
han_relations.roles_role, 
han_relations.group_id , 
han_gruop.title as grupo , 
han_relations.vistas_id , 
han_views.title as vista , 
han_relations.active 

from han_relations

left join han_gruop on han_gruop.id = han_relations.group_id
left join han_views on han_views.id = han_relations.vistas_id

WHERE han_relations.roles_role = 'ADMIN_ADMIN'
*/

    public function findRoleByRol(Request $request , Response $response , $role)
    {
        $getRoleByRol = $this->relations
                                ->selectRaw("han_relations.roles_role,
                                    han_relations.group_id,
                                    han_gruop.title as grupo,
                                    han_relations.vistas_id,
                                    han_views.title as vista,
                                    han_relations.active")
                                ->leftjoin("han_gruop" , "han_relations.group_id" , "=" , "han_gruop.id")
                                ->leftjoin("han_views" , "han_relations.vistas_id" , "=" , "han_views.id")
                                ->where("han_relations.roles_role" , "=" , $role["role"])
                                ->orderBy("han_relations.group_id")
                                ->get();

        $this->customResponse->is200Response($response , $getRoleByRol);
    }
/*
*buscar menu de acuerdo al correo
*/

     public function findSidebarByRol(  $email)
     {
          //NAME = role => ADMIN_ADMIN

         $responseMenssage = array();
        /**
         * Consultamos las vistas de la tabla relations
         * select * from han_relations 
         *inner join han_gruop on han_gruop.id = han_relations.group_id where roles_role = 'ADMIN_ADMIN' AND active = 'Y' group by group_id
         */
        /*
        select 
        han_relations.id as id_relations, 
        han_relations.roles_role, 
        han_relations.group_id,
        han_relations.vistas_id,
        han_relations.active,
        han_gruop.id as id_gruop,
        han_gruop.title,
        han_gruop.icono
        from han_relations
        inner join han_gruop on han_gruop.id = han_relations.group_id
        inner join users on users.role = han_relations.roles_role
        WHERE users.email = 'seguridadst@internetinalambrico.com.co' AND han_relations.active = 'Y'
        group by han_relations.group_id
        */
        /*$relationsRole = $this->relations
                    ->leftjoin("han_gruop" , "han_relations.group_id" , "=" , "han_gruop.id")
                    ->where(["roles_role"=>$role])
                    ->where("active" , "=" , "Y")
                    ->groupBy("group_id")
                    ->get(); */

        $relationsRole = $this->relations
                        ->selectRaw(" han_relations.id as id_relations, han_relations.roles_role, han_relations.group_id, han_relations.vistas_id, han_relations.active,
                                        han_gruop.id as id_gruop,
                                        han_gruop.title,
                                        han_gruop.icono")
                        ->leftjoin("han_gruop" , "han_relations.group_id" , "=" , "han_gruop.id")
                        ->leftjoin("users"      , "users.role" , "=" , "han_relations.roles_role")
                        ->where(["users.email" => $email])
                        ->where("han_relations.active" , "=" , "Y")
                        ->groupBy("group_id")
                        ->get();
                    
         foreach($relationsRole as $group)
         {
            $temp = [
                "title" => $group->title,
                "icono" => $group->icono,
                "submenu" => $this->findVistaByGroup($group->group_id , $group->roles_role)
            ];
            array_push($responseMenssage , $temp);
         }

         return  $responseMenssage;

     }

     /**
      *armar vistar de acuerdo al grupo 
      */

     public function findVistaByGroup($group_id , $role)
     {
        /**
         * * select * from han_relations 
         *inner join han_views on han_views.id = han_relations.vistas_id where group_id = '2' AND active = 'Y' */
        $vistas = $this->relations
                        ->selectRaw("title, url")
                        ->leftjoin("han_views" , "han_views.id" , "=" , "han_relations.vistas_id")
                        ->where("group_id","=", $group_id)
                        ->where("active" , "=" , "Y")
                        ->where("roles_role" , "=" , $role)
                        ->get();
        
        return $vistas;
     }
     /*
     *GET lista de grupos
     */
     public function findByGroup(Request $request , Response $response)
     {
            $getGroup = $this->group->get();

            $this->customResponse->is200Response($response , $getGroup);
     }
     /*
     *POST save group
     */

     public function saveGroup(Request $request , Response $response)
     {
        $this->validator->validate($request , [
            "title" => v::notEmpty(),
            "icono" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMenssage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMenssage);
        }

        $this->group->create([
            "title" => CustomRequestHandler::getParam($request , "title"),
            "icono" => CustomRequestHandler::getParam($request , "icono")
                    ]);
        $responseMenssage = "creado";

        $this->customResponse->is200Response($response , $responseMenssage);
     }

     /*
     *GET buscar vistas
     */
     public function findByViews(Request $request , Response $response)
     {
        $getListViews = $this->view->get();

        $this->customResponse->is200Response($response  , $getListViews);
     }

     /*
     *POST save vistas
     */
     public function saveView(Request $request , Response $response)
     {
        $this->validator->validate($request , [
            "title"=> v::notEmpty(),
            "url" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMenssage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMenssage);
        }

        $this->view->create([
            "title" => CustomRequestHandler::getParam($request , "title"),
            "url"   => CustomRequestHandler::getParam($request , "url")
        ]);

        $responseMenssage = "creado";

        $this->customResponse->is200Response($response , $responseMenssage);

     }





     

}