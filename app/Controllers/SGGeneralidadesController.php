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

	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request  , [

			"nombre" => v::notEmpty(),
			"tipo" => v::notEmpty(),
			"item" => v::notEmpty(),
			"id_empresa" => v::notEmpty(),
		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$this->generalidades->create([
			"nombre" => CustomRequestHandler::getParam($request , "nombre"),
			"tipo" 	 => CustomRequestHandler::getParam($request , "tipo"),
			"id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
			"item" => CustomRequestHandler::getParam($request , "item")
		]);

		$responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	public function tipoDisct(Request $request , Response $response , $id)
	{
		$getDistTipo = $this->generalidades
							->selectRaw("DISTINCT tipo")
							->where(["id_empresa" => $id])
							->get();

		$this->customResponse->is200Response($response , $getDistTipo);

	}

	public function findByTipo(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"tipo" => v::notEmpty(),
			"id_empresa" => v::notEmpty()
		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response  , $responseMessage);
		}

		$getFindByTipo = $this->generalidades
								->where(["tipo" => CustomRequestHandler::getParam($request , "tipo")])
								->where(["id_empresa"=> CustomRequestHandler::getParam($request , "id_empresa")])
								->get();

		$this->customResponse->is200Response($response , $getFindByTipo);


	}
}



?>