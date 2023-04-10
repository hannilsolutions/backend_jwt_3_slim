<?php

namespace App\Controllers;

use App\Models\SGFirma; 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator; 


class SGFirmaController
{

	private $customResponse;

	private $firma;

	private $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->firma = new SGFirma();

		$this->validator = new Validator();
	}


	/**
	 *ENDPOINT SAVE
	 * */
	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [ 
			"id_user" 	=> v::notEmpty(),
			"cargo" 	=> v::notEmpty(),
			"id_empresa" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			

			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		try{

			//save

			$this->firma->save([ 
				"cargo" => CustomRequestHandler::getParam($request , "cargo"),
				"id_user" => CustomRequestHandler::getParam($request , "id_user"),
				"id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
				"estado" => "ACTIVO"
			]);

			$responseMessage = "creado";

			$this->customResponse->is200Response($response , $responseMessage);

		}catch(Exception $e)
		{
			return $this->customResponse->is400Response($response , $e->getMessage());
		}
	}

	/**
	 * ENDPOINT GET*/
	public function getFindByIdEmpresa(Request $request , Response  $response , $id)
	{
			$getList = $this->firma->join("users" , "users.id" , "=" , "han_sg_firmas.id_user")->join("han_sg_empresa" , "han_sg_empresa.id_empresa", "=" , "han_sg_firmas.id_empresa")->where(["id_empresa" => $id])->get();

			$this->customResponse->is200Response($response , $getList);
	}

	/**
	 * endpoint get*/
	public function findAll(Request $request  , Response $response)
	{
		$getList = $this->firma->join("users" , "users.id" , "=" , "han_sg_firmas.id_user")->join("han_sg_empresa" , "han_sg_empresa.id_empresa", "=" , "han_sg_firmas.id_empresa")->get();

		$this->customResponse->is200Response($response , $getList);
	}

}

?>