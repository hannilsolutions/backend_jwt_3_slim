<?php

namespace App\Controllers;

use App\Models\Roles;
use App\Models\Han_Relations;
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

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->roles = new Roles();

         $this->validator = new Validator();

         $this->relations = new Han_Relations();
    }

     public function findByRole(Request $request , Response $response)
     {
         $roles = $this->roles->get();

         $responseMenssage = ["roles" => $roles];

         $this->customResponse->is200Response($response ,  $responseMenssage);
     }

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
        WHERE users.email = 'web@internetinalambrico.com.co' AND han_relations.active = 'Y'
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
                "submenu" => $this->findVistaByGroup($group->group_id)
            ];
            array_push($responseMenssage , $temp);
         }

         return  $responseMenssage;

     }

     /**
      * */

     public function findVistaByGroup($group_id)
     {
        /**
         * * select * from han_relations 
         *inner join han_views on han_views.id = han_relations.vistas_id where group_id = '2' AND active = 'Y' */
        $vistas = $this->relations
                        ->selectRaw("title, url")
                        ->leftjoin("han_views" , "han_views.id" , "=" , "han_relations.vistas_id")
                        ->where("group_id","=", $group_id)
                        ->where("active" , "=" , "Y")
                        ->get();
        
        return $vistas;
     }



     

}