<?php

namespace App\Controllers;

use App\Models\SGControles;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGControlesController
{
	protected $customResponse;

	protected $sgControles;

	protected $validator;

	public function __construct()
	{
		$this->customResponse 	= new CustomResponse();

		$this->sgControles 		= new SGControles();

		$this->validator 		= new Validator();
	}

/*
*POST -> save controles
*/
	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"nombre"	=>	v::notEmpty(),
			"id_peligro"=>	v::notEmpty()]);
		if($this->validator->failed())
	{
		$responseMenssage = $this->validator->errors;

		return $this->customResponse->is400Response($response, $responseMenssage);
	}

	//si la validacion es falsa se procede a guardar
	$this->sgControles->create([
		"nombre" => CustomRequestHandler::getParam($request , "nombre"),
		"id_peligro" => CustomRequestHandler::getParam($request , "id_peligro")
	]);

	$responseMenssage = "creado";

	$this->customResponse->is200Response($response , $responseMenssage);

	}

/*
*GET -> get controles findByPeligro
*/
	public function findByPeligro(Request $request , Response $response , $id)
	{
		$getControles = $this->sgControles
							->where(["id_peligro" => $id])
							->get();

		$this->customResponse->is200Response($response , $getControles);
	}

/*
* DELETE control por el id_control
*/
	public function deleteById(Request $request , Response $response , $id)
	{
		$deteleById = $this->sgControles
							->where(["id_control"	=>	$id])
							->delete();
							
		$responseMenssage = "eliminado";

		$this->customResponse->is200Response($response , $responseMenssage);
	}
	


}

?>