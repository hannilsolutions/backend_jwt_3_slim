<?php


namespace App\Controllers;

use App\Models\User;
use App\Controllers\RolesController;
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

    protected   $rol;

    public function  __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->user = new User();

        $this->validator = new Validator();

        $this->rol = new RolesController();
    }

    ##validat token despues de loguin pero con mail
    public function Validate(Request $request , Response $response , $jwt)
    {
        $getDecodeJWT = GenerateTokenController::decodeToken($jwt["jwt"]);

        $responseMessage = $jwt["jwt"];

        #enviar información usuario
       $getUsuario = $this->getUsuario($getDecodeJWT->jti);

       #recuperar menu del logueado
       $getMenu = $this->getMenu($getDecodeJWT->jti);

       return $this->customResponse->is200ResponseLogin($response,$responseMessage , $getUsuario , $getMenu);
   
    }
    //ENDPOTIN POST Registrar uusuario
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
            "id_empresa"=>CustomRequestHandler::getParam($request , "id_empresa"),
            "password"=>$passwordHash
        ]);

        $responseMessage ="usuario creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

//function para encriptar contraseña
    public  function hashPassword($password)
  {
    return password_hash($password,PASSWORD_DEFAULT);
  }
//validar si existe un correo

    public function EmailExist($email)
    {
    $count =  $this->user->where(["email"=>$email])->count();

    if($count==0)
    {
        return false;
    }
    return true;
    }
//ENDP POINT POST -> login generación de toke, menu y datos de usuario

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
       $email = CustomRequestHandler::getParam($request,"email");

       $verifyAccount = $this->verifyAccount(CustomRequestHandler::getParam($request,"password"), $email);

       if($verifyAccount==false)
       {
           $responseMessage ="Error de sus credenciales";

           return $this->customResponse->is400Response($response,$responseMessage);
       }
       $verifyActive    = $this->verifyActive($email);
       
       #validacion para ver si el cliente se encuentra activo
       if($verifyActive==false){
           $responseMessage = "usuario inactivo";

           return $this->customResponse->is400Response($response , $responseMessage);
       }



       #enviar información usuario
       $getUsuario = $this->getUsuario($email);
       foreach($getUsuario as $item)
       {
            $this->user->where("id" , "=" , $item->id)->update([
                "last_login"  => date("Y-m-d H:i:s")
            ]);
       }
       #recuperar menu del logueado
       $getMenu = $this->getMenu($email);
       #generación de token
       $responseMessage = GenerateTokenController::generateToken($email);
       return $this->customResponse->is200ResponseLogin($response,$responseMessage , $getUsuario , $getMenu);
    }

    #function informacion del usuario
    public function getUsuario($email)
    {
        $usuario = $this->user->selectRaw('id, user,marca,active,email,url_img,role,created_at,updated_at, id_empresa')
                                ->where(["email"=>$email])->get();
        return $usuario;
    }
    #function menu del cliente por correo
    public function getMenu($email)
    {
      $menu = $this->rol->findSidebarByRol($email);

      return $menu;
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

    /**
     * ENDPOINT POST recovery*/
    public function recovery(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "email" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        //validar si existe el correo
            $existMail = $this->EmailExist(CustomRequestHandler::getParam($request , "email"));

            if (!$existMail) {

                $responseMessage = "Correo inválido";

                return $this->customResponse->is400Response($response , $responseMessage);
            }
        //validar si esta activo
            $mailActivo = $this->verifyActive(CustomRequestHandler::getParam($request , "email"));

            if (!$mailActivo) {
                
                $responseMessage = "Correo Inactivo";

                return $this->customResponse->is400Response($response , $responseMessage);
            }

            //recuperar infor de cliente
            $getUser = $this->getUsuario(CustomRequestHandler::getParam($request , "email"));

            $id = '';

            foreach($getUser as $item)
            {
                $id = $item->id;
            }
            $token = $this->generateTokenFirma();
            
            $this->mailRecovery(CustomRequestHandler::getParam($request , "email") , $token);



            //generar token
            $this->user->where("id" , "=" , $id)->update([
                "token_pw" => $token ,
                "fecha_caducidad" => date("Y-m-d H:i:s")
            ]);


            $responseMessage =  GenerateTokenController::generateToken(CustomRequestHandler::getParam($request , "email"));

            $this->customResponse->is200Response($response , $responseMessage);
    }

    public function mailRecovery($destination , $token)
    {
        //// Para enviar un correo HTML, debe establecerse la cabecera Content-type
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // Cabeceras adicionales
        $cabeceras .= 'From: HannilPro <sistemas@hannilsolutions.com>' . "\r\n";

        // Enviarlo

        $subject = 'Código de recuperación de contraseña';

        $msm = 'Apreciado Cliente:<br>
                Reciba una cordial saludo.<br>
                este es su código de validacion: '.$token.' para recuperar su cuenta.';
        $msm = wordwrap($msm , 70);

        if(!mail($destination ,$subject , $msm , $cabeceras))
        {
            return false;
        }

        return true;

    }

    /**
     * ENDPOINT POST VALIDATE TOKEN
    */
    public function tokenValidateRecovery(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "token" => v::notEmpty(),
            "email" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $email = CustomRequestHandler::getParam($request , "email");

        $token = CustomRequestHandler::getParam($request , "token");

        #traemos info de cliente
        $getUser = $this->user->selectRaw("token_pw , fecha_caducidad")->where("email" , "=" , $email)->get();

        $user = array();

        foreach($getUser as $item)
        {
            $user["token_pw"] = $item->token_pw;

            $user["fecha"] = $item->fecha_caducidad;
        }

        #fecha de expiracion
        $fecha_caducidad = date("Y-m-d H:i:s");

        #reduccion de fechacliente con fecha caducidad
        $residuoFechas = strtotime($fecha_caducidad) - strtotime($user["fecha"]);

        #validamos el valor inferior a 120 segundo
        if($residuoFechas > 360)
        {
            $responseMessage = "Token Expired";

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        #validamos si el token es igual al enviado
        $token_pw = $token;

        if($user["token_pw"] != $token_pw)
        {
            $responseMessage = "token errado";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $responseMessage = "validado";

        $this->customResponse->is200Response($response  , $responseMessage);
    }

    /**
     * ENDPOINT POST NUEVA PASSWORD
     * */
    public function newPassword(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "password" => v::notEmpty(),
            "email" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $email = CustomRequestHandler::getParam($request , "email");
        //buscar cliente
        $user = $this->user->where("email" , "=" , $email)->get();

        foreach($user as $item)
        {
            $id = $item->id;
        }

        $passwordHash = $this->hashPassword(CustomRequestHandler::getParam($request  , "password"));

        $this->user->where("id" , "=" ,$id)->update([
            "password" => $passwordHash
        ]);

        $responseMessage = "contraseña actualizada";

        $this->customResponse->is200Response($response , $responseMessage );


    }


    public function generateTokenFirma()
    {
        $bytes = openssl_random_pseudo_bytes(3);
        $hex   = bin2hex($bytes);

        return $hex;
    }


}

?>