<?php

namespace App\Controllers;

use App\Models\Marcas;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class MarcasController
{
	protected $customResponse;

	protected $marcas;

	protected $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->marcas = new Marcas();

		$this->validator = new Validator();
	}

	/*
	*ENDPOINT POST SAVE
	*/
	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"marca_nombre" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		if($this->validate_exist(CustomRequestHandler::getParam($request , "marca_nombre")))
		{
			$responseMessage = "ya existe la marca";

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$this->marcas->create([
			"marca_nombre" => CustomRequestHandler::getParam($request , "marca_nombre")
		]);

		$responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/**
	 * ENDPOINT GET LIST
	 * */
	public function list(Request $request , Response $response)
	{
		$getMarcas = $this->marcas->get();

		$this->customResponse->is200Response($response , $getMarcas);
 	}

 	/***
 	 * ENDPOINT DELETE
 	*/
 	public function delete(Request $request , Response $response , $id)
 	{
 		$this->marcas->delete->where(["id_marca" => $id])->delete();

 		$responseMessage = "eliminado";

 		$this->customResponse->is200Response($response , $responseMessage );
 	}


	public function validate_exist($marca)
	{
		$getFindByName = $this->marcas->where("marca_nombre" , "=" , $marca)->count();

		if ($getFindByName > 0) {
			
			return true;
		}else {
			return false;
		}
	}
}

?>