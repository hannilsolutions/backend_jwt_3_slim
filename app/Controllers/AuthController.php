<?php


namespace App\Controllers;

use App\Models\User;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class AuthController
{

    protected  $customResponse;

    protected  $user;

    protected  $validator;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->user = new User();

        $this->validator = new Validator();
    }

    public function Register(Request $request,Response $response)
    {
        $this->validator->validate($request,[
            "user"=>v::notEmpty(),
            "email"=>v::notEmpty()->email(),
            "password"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        if($this->EmailExist(CustomRequestHandler::getParam($request,"email")) )
        {
            $responseMessage = "el email ya se encuentra registrado";
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $passwordHash = $this->hashPassword(CustomRequestHandler::getParam($request,"password"));

        $this->user->create([
           "user"=>CustomRequestHandler::getParam($request,"user"),
            "email"=>CustomRequestHandler::getParam($request,"email"),
            "password"=>$passwordHash
        ]);

        $responseMessage ="usuario creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }


    public  function hashPassword($password)
  {
    return password_hash($password,PASSWORD_DEFAULT);
  }

    public function EmailExist($email)
    {
    $count =  $this->user->where(["email"=>$email])->count();

    if($count==0)
    {
        return false;
    }
    return true;
    }


    public function Login(Request $request, Response $response)
    {
       $this->validator->validate($request,[
          "email"=>v::notEmpty()->email(),
          "password"=>v::notEmpty()
       ]);

       if($this->validator->failed())
       {
           $responseMessage = $this->validator->errors;
           return $this->customResponse->is400Response($response,$responseMessage);
       }

       $verifyAccount = $this->verifyAccount(CustomRequestHandler::getParam($request,"password"),
                                               CustomRequestHandler::getParam($request,"email"));

       if($verifyAccount==false)
       {
           $responseMessage ="Error de sus credenciales";

           return $this->customResponse->is400Response($response,$responseMessage);
       }
       $verifyActive    = $this->verifyActive(CustomRequestHandler::getParam($request , "email"));
       
       #validacion para ver si el cliente se encuentra activo
       if($verifyActive==false){
           $responseMessage = "usuario inactivo";

           return $this->customResponse->is400Response($response , $responseMessage);
       }

       #generación de token
       $responseMessage = GenerateTokenController::generateToken(CustomRequestHandler::getParam($request,"email"));

       return $this->customResponse->is200Response($response,$responseMessage);
    }

    #validar si el usuario se encuetra activo
    public function verifyActive($email){

        $active = "";

        $user = $this->user->where(["email"=>$email])->get();

        foreach($user as $key)
        {
            $active = $key->active;
        }
        if($active==0)
        {
            return false;
        }
            return true;
        
    }

    #validar email y contraseña de cliente
    public function verifyAccount($password,$email)
    {
        $hashPassword ="";
        
        $count = $this->user->where(["email"=>$email])->count();

        if($count==false)
        {
            return false;
        }

        $user = $this->user->where(["email"=>$email])->get();

        foreach ($user as $users)
        {
            $hashPassword = $users->password;
             
        }

        $verify = password_verify($password,$hashPassword);

        if($verify==false)
        {
            return false;
        }

        return true;
    }

}