<?php

namespace  App\Response;

class CustomResponse
{

    public function is200Response($response,$responseMessage)
    {
        $responseMessage = json_encode(["success"=>true,"response"=>$responseMessage]);
        $response->getBody()->write($responseMessage);
        return $response->withStatus(200)
            ->withHeader("Content-Type","application/json")
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
    }

    public function is200Pdf($response , $pdf)
    {
        $response->getBody()->write($pdf);
        return $response->withStatus(200)
                ->withHeader("Content-Type" , "application/pdf")
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
    }

    public function is200ResponseLogin($response , $responseMessage , $usuario , $menu)
    {
        $responseMessage = json_encode(
            ["success" => true,
            "response" => $responseMessage,
            "usuario"  => $usuario,
            "menu"     => $menu,
        ]
        );
        $response->getBody()->write($responseMessage);
        return $response->withStatus(200)
                ->withHeader("Content-Type" , "application/json")
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
        
            
    }


    public function is400Response($response,$responseMessage)
    {
        $responseMessage = json_encode(["success"=>false,"response"=>$responseMessage]);
        $response->getBody()->write($responseMessage);
        return $response->withStatus(400)
            ->withHeader("Content-Type","application/json")
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
    }

    public function is404Response($response,$responseMessage)
    {
        $responseMessage = json_encode(["success"=>false,"response"=>$responseMessage]);
        $response->getBody()->write($responseMessage);
        return $response->withStatus(404)
            ->withHeader("Content-Type","application/json")
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
    }

    public function is422Response($response,$responseMessage)
    {
        $responseMessage = json_encode(["success"=>true,"response"=>$responseMessage]);
        $response->getBody()->write($responseMessage);
        return $response->withStatus(422)
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,Accept,Origin,Authorization');
    }
}