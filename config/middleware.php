<?php
<?php
return function ($app)
{
    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "ignore"=>["/auth/login", "/contratos/findByCus","/contratos/gps/save" , "/auth/recoveryT" , "/auth/recovery" , "/auth/recoveryP" ],
        "secret"=> SECRET_PASSWORD,
        
        // ← AGREGAR ESTO: buscar el token en múltiples lugares
        "before" => function($request, $arguments) {
            return $request;
        },
        "token" => function($request) {
            // Forma 1: header normal
            $header = $request->getHeaderLine("Authorization");
            
            // Forma 2: Apache reescribe el header
            if (empty($header)) {
                $server = $request->getServerParams();
                $header = $server["HTTP_AUTHORIZATION"] 
                       ?? $server["REDIRECT_HTTP_AUTHORIZATION"] 
                       ?? "";
            }
            
            if (preg_match("/Bearer\s+(.*)$/i", $header, $matches)) {
                return $matches[1];
            }
            return null;
        },
        
        "error"=> function ($response,$arguments)
        {
            $data["success"]= false;
            $data["response"]=$arguments["message"];
            $data["status_code"] = "401";
            return $response->withHeader("Content-type","application/json")
                ->getBody()->write(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));

    $app->add(function ($req,$res,$next){
       $response = $next($req,$res);
      return $response->withHeader("Access-Control-Allow-Origin","*")
           ->withHeader("Access-Control-Allow-Headers","X-Requested-With,Content-Type,Accept,Origin,Authorization")
           ->withHeader("Access-Control-Allow-Methods","GET,POST,PUT,PATCH,OPTIONS,DELETE")
           ->withHeader("Access-Control-Allow-Credentials","true");
    });
};
/*
#use App\Interfaces\SecretKeyInterface as Secret;


return function ($app)
{
    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "ignore"=>["/auth/login", "/contratos/findByCus","/contratos/gps/save" , "/auth/recoveryT" , "/auth/recovery" , "/auth/recoveryP" ],
        "secret"=>SECRET_PASSWORD,
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
};*/