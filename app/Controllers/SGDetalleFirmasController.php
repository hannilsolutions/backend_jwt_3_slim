<?php

namespace App\Controllers;

use App\Models\SGDetalleFirmas;
use App\Models\SGFirma;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGDetalleFirmasController
{
    protected $detalle;

    protected $validator;

    protected $customResponse;

    protected $firma;

    public function __construct()
    {
        $this->detalle = new SGDetalleFirmas();

        $this->validator = new Validator();

        $this->customResponse = new CustomResponse();

        $this->firma = new SGFirma();
    }

    public function listByIdPermiso(Request $request , Response $response , $id)
    {
        /**SELECT dfr.id, dfr.url_firma , dfr.updated_at, users.id id_user, users.user , users.url_img  FROM  han_sg_detalle_firma dfr
                inner join `han_sg_firmas` fr on fr.id = dfr.id_firma
                inner join `users` on users.id = fr.id_user 
                where dfr.id_permiso = 3 ; */
            $get = $this->detalle->selectRaw("han_sg_detalle_firma.id , han_sg_detalle_firma.url_firma , han_sg_detalle_firma.updated_at,
                                                users.id id_user , users.user , users.url_img")
                                                ->join("han_sg_firmas" , "han_sg_firmas.id" , "=" , "han_sg_detalle_firma.id_firma")
                                                ->join("users" , "users.id" , "=" , "han_sg_firmas.id_user")
                                                ->where(["han_sg_detalle_firma.id_permiso" => $id])
                                                ->get();

            $this->customResponse->is200Response($response , $get);
    }

    public function create(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),            
            "id_empresa" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        //buscar si tiene firmas
        $getFirmas = $this->firma->where("id_empresa" , "=" , CustomRequestHandler::getParam($request , "id_empresa"))->get();;
        if($getFirmas->count() == 0)
        {
            $responseMessage = "No tiene personal habilitado para firmar";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        try{

            foreach($getFirmas as $item)
            {
                $this->detalle->create([
                    "id_firma" => $item->id,
                    "id_permiso" => CustomRequestHandler::getParam($request , "id_permiso")
                ]);
            }
        $responseMessage = "creado";

        $this->customResponse->is200Response($response , $responseMessage);

        }catch(Exception $e)
        {
                $responseMessage = $e->getMessage();

                $this->customResponse->is400Response($response , $responseMessage);
        }

    }
}