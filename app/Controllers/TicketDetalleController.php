<?php

namespace App\Controllers;

use App\Models\TicketDetalle;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class TicketDetalleController
{

    protected  $customResponse;

    protected  $detalle;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->detalle = new TicketDetalle();

         $this->validator = new Validator();
    }

/**
 * ENDPOINT SAVE*/
    public function create(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "id_user"=>v::notEmpty(),
           "comentario"=>v::notEmpty(),
           "id_ticket"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->detalle->create([
           "id_user"=>CustomRequestHandler::getParam($request,"id_user"),
        "comentario"=>CustomRequestHandler::getParam($request,"comentario"),
        "id_ticket"=>CustomRequestHandler::getParam($request,"id_ticket"),
        "fecha" => date("Y-m-d")
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    /**
     * ENDPOINT GET {ID_TICKET}*/

    public function findByIdTicket(Request $request,Response $response , $id)
    {
       $list = $this->detalle->selectRaw("
            ticket_detalle.id,
            ticket_detalle.id_user,
            ticket_detalle.comentario,
            ticket_detalle.fecha,
            ticket_detalle.created_at
            users.user
        ")
       ->join("users" , "users.id" , "=" , "ticket_detalle.id_user")
       ->where(["ticket_detalle.id_ticket" => $id])
       ->get();

        $this->customResponse->is200Response($response,$list);
    }

/*
    public function getSingleGuest(Request $request,Response $response,$id)
    {

        $singleGuestEntry = $this->guestEntry->where(["id"=>$id])->get();

        $this->customResponse->is200Response($response,$singleGuestEntry);
    }

    public function editGuest(Request $request,Response $response,$id)
    {

        $this->validator->validate($request,[
            "name"=>v::notEmpty(),
            "email"=>v::notEmpty()->email(),
            "comments"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }


        $this->guestEntry->where(['id'=>$id])->update([
            "full_name"=>CustomRequestHandler::getParam($request,"name"),
            "email"=>CustomRequestHandler::getParam($request,"email"),
            "comment"=>CustomRequestHandler::getParam($request,"comments"),
        ]);
        $responseMessage = "guest entry data updated successfully";

        $this->customResponse->is200Response($response,$responseMessage);
    }

    public function deleteGuest(Request $request,Response $response,$id)
    {
        $this->guestEntry->where(["id"=>$id])->delete();

        $responseMessage = "guest entry data deleted successfully";

        $this->customResponse->is200Response($response,$responseMessage);
    }

    public function countGuests(Request $request,Response $response)
    {
        $guestsCount = $this->guestEntry->count();

        $this->customResponse->is200Response($response,$guestsCount);
    }*/

}

?>