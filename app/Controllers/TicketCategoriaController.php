<?php

namespace App\Controllers;

use App\Models\TicketCategoria;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class TicketCategoriaController
{

    protected  $customResponse;

    protected  $categoria;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->categoria = new TicketCategoria();

         $this->validator = new Validator();
    }

    public function create(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "name"=>v::notEmpty(), 
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $this->categoria->create([
           "name"=>CustomRequestHandler::getParam($request,"name")
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

    }


    /**
     * ENDPOINT GE*/

    public function list(Request $request,Response $response)
    {
       $list = $this->categoria->get();

        $this->customResponse->is200Response($response,$list);
    }


    /*public function getSingleGuest(Request $request,Response $response,$id)
    {

        $singleGuestEntry = $this->guestEntry->where(["id"=>$id])->get();

        $this->customResponse->is200Response($response,$singleGuestEntry);
    }*/

   /* public function editGuest(Request $request,Response $response,$id)
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