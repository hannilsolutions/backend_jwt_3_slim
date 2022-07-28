<?php

namespace App\Controllers;

use App\Models\SGTipoTrabajo; 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGTipoTrabajoController
{
    protected $customResponse;

    protected $sgTipoTrabajo;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->sgTipoTrabajo = new SGTipoTrabajo();

        $this->validator = new Validator();
    }

    /**
     * ENDOPOINT GET 
     */
    public function getTipoTrabajo(Request $request , Response $response , $id)
    {
        $getTipoTrabajo = $this->sgTipoTrabajo->where(["id_empresa" => $id["id"]])->get();

        $this->customResponse->is200Response($response , $getTipoTrabajo);
    }
}