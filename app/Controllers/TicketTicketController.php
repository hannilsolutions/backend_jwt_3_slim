<?php

namespace App\Controllers;

use App\Models\TicketTicket;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class TicketTicketController
{

    protected  $customResponse;

    protected  $ticket;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->ticket = new TicketTicket();

         $this->validator = new Validator();
    }

    /**
     * ENDPOITN POST*/

    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
          
        "titulo" => v::notEmpty(),
        "id_categoria" => v::notEmpty(), 
        "comentario" => v::notEmpty(), 
        "id_user" => v::notEmpty(),
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->ticket->create([
           "titulo"=>CustomRequestHandler::getParam($request,"titulo"),
        "id_categoria"=>CustomRequestHandler::getParam($request,"id_categoria"),
        "comentario"=>CustomRequestHandler::getParam($request,"comentario"),
        "id_user"=>CustomRequestHandler::getParam($request,"id_user"),
        "fecha" => date("Y-m-d"),
        "estado" => 1
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    /**
     * ENDPOITN GET LIST*/

    public function listEstado(Request $request,Response $response , $id)
    {
       $ticketPendientes = $this->ticket->selectRaw("
        ticket_ticket.id,
        ticket_ticket.titulo,
        ticket_ticket.fecha,
        ticket_ticket.comentario,
        users.user ,
        ticket_categoria.name,
        ticket_ticket.created_at,
        ticket_ticket.updated_at")
       ->join("ticket_categoria" , "ticket_categoria.id" , "=" , "ticket_ticket.id_categoria")
       ->join("users" , "users.id" , "=" , "ticket_ticket.id_user")
       ->where(["ticket_ticket.estado" => $id])->get();

        $this->customResponse->is200Response($response,  $ticketPendientes);
    } 

}

?>