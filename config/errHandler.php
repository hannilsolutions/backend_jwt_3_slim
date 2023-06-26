<?php
$container["errorHandler"] = function ($container)
{
    return function ($request,$response,$exception) use ($container)
    {
        $statusCode = 500;
        $errorMessage = "INTERNAL_ERROR";
        $message = "Algo ha ocurrido internamente";

        
        if ($exception instanceof \PDOException) {
            $statusCode = 500;
            $errorMessage = "DATABASE_ERROR " . $exception->getMessage();
            $message = "Error en la base de datos";
        } elseif ($exception instanceof \InvalidArgumentException) {
            $statusCode = 400;
            $errorMessage = "INVALID_ARGUMENT";
            $message = "Argumento inválido";
        }

        return $response->withStatus($statusCode)
        ->withHeader('Content-Type', 'application/json')
        ->write(json_encode([
            "success" => false,
            "error" => $errorMessage,
            "message" => $message,
            "status_code" => $statusCode,
            'trace' => $exception->getTraceAsString()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    };
};


$container["notFoundHandler"] = function ($container)
{
    return function ($request,$response,$exception) use ($container)
    {
        return $response->withStatus(404)
            ->withHeader('Content-Type','application/json')
            ->write(json_encode(
                array(
                    "success"=>false,
                    "error"=>"NOT_FOUND",
                    "message"=>"EndPoint no fue encontrado",
                    "status_code"=>"404",
                ),
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ));
    };
};



$container["notAllowedHandler"] = function ($container)
{
    return function ($request,$response,$exception) use ($container)
    {
        return $response->withStatus(405)
            ->withHeader('Content-Type','application/json')
            ->write(json_encode(
                array(
                    "success"=>false,
                    "error"=>"NOT_ALLOWED",
                    "message"=>"esta solicitud no está permitida en esta ruta",
                    "status_code"=>"405",
                ),
                JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ));
    };
};




$container['phpErrorHandler'] = function($container)
{
  return $container["errorHandler"];
};