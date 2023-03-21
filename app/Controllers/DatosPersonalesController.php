<?php

namespace App\Controllers;

use App\Models\DatosPersonales;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class DatosPersonalesController
{

	private $customResponse;

	private $datosPersonalesModel;

	private $validatos;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->validator = new Validator();

		$this->datosPersonales = new DatosPersonales();
	}

	public function save(Request $request , Response $response)
	{	
		$this->validator->validate($request , [

			"id_user" => v::notEmpty(),
			"tipo_documento" => v::notEmpty(),
			"documento" => v::notEmpty(),
			"cargo" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMenssage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMenssage);
		}

		try
		{
			$this->datosPersonales->create([
				"id_user" => CustomRequestHandler::getParam($request , "id_user"),
				"tipo_documento" => CustomRequestHandler::getParam($request , "tipo_documento"),
				"documento" => CustomRequestHandler::getParam($request , "documento"),
				"cargo" => CustomRequestHandler::getParam($request , "cargo")
			]);

			$responseMessage = "creado",

			$this->customResponse->is200Response($response , $responseMessage);

		}catch(Exception $e)
		{
			$responseMessage = $e->getMessage();

			return $this->customResponse->is400Response($response , $responseMessage);
		}
	}

	public function findById(Request $request , Response $response , $id)
	{
		$getList = $this->datosPersonales->where(["id_user" => $id])->get();

		$this->customResponse->is200Response($response , $getList);
	}

	public function updated(Request $request , Response $response , $id)
	{
		$this->validator->validate($request , [

			"id_user" => v::notEmpty(),
			"tipo_documento" => v::notEmpty(),
			"documento" => v::notEmpty(),
			"cargo" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMenssage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMenssage);
		}

		try{

		$this->datosPersonales->where(["id" => $id])->update([
		"id_user" => CustomRequestHandler::getParam($request , "id_user"),
		"tipo_documento" => CustomRequestHandler::getParam($request , "tipo_documento"),
		"documento" => CustomRequestHandler::getParam($request , "documento"),
		"cargo" => CustomRequestHandler::getParam($request , "cargo")
		]);
		}catch(Exception $e)
		{
		$responseMessage = $e->getMessage();

			return $this->customResponse->is400Response($response , $responseMessage);
		
		}
	}

}

?>