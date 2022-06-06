<?php
#use App\Interfaces\SecretKeyInterface as Secret;


return function ($app)
{
    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "ignore"=>["/auth/login","/contratos/saveEncuesta" , "/contratos/findByCus","/contratos/gps/save"],
        "secret"=>"d3fc3547346f0ef9cc47b9d5951912559bda2322ed3a2794d0ae49f76110dc61",
        "error"=>function ($response,$arguments)
        {
            $data["success"]= false;
            $data["response"]=$arguments["message"];
            $data["status_code"] = "401";

            return $response->withHeader("Content-type","application/json")
                ->getBody()->write(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));


    $app->add(Function ($req,$res,$next){
       $response = $next($req,$res);
      return $response->withHeader("Access-Control-Allow-Origin","*")
           ->withHeader("Access-Control-Allow-Headers","X-Requested-With,Content-Type,Accept,Origin,Authorization")
           ->withHeader("Access-Control-Allow-Methods","GET,POST,PUT,PATCH,OPTIONS,DELETE")
           ->withHeader("Access-Control-Allow-Credentials","true");
    });
};