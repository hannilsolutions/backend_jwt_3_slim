<?php

namespace App\Controllers;

use App\Models\SGGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;


class SGGeneralidadesController
{
	protected $customResponse;

	protected $generalidades;

	protected $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->generalidades = new SGGeneralidades();

		$this->validator = new Validator();
	}

	public function tipoDisct(Request $request , Response $response , $id)
	{
		$getDistTipo = $this->generalidades
							->selectRaw("DISTINCT tipo")
							->where(["id_empresa" => $id])
							->get();
							
		$this->customResponse->is200Response($response , $getDistTipo);

	}
}



?>