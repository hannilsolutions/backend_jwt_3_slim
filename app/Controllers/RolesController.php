<?php

namespace App\Controllers;

use App\Models\Roles;
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

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->roles = new Roles();

         $this->validator = new Validator();
    }

     public function findByRole(Request $request , Response $response)
     {
         $roles = $this->roles->get();

         $this->CustomResponse->is200Response($response ,  $roles);
     }



     

}