<?php

namespace App\Controllers;

use App\Models\SGEmpresa; 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Exception;


class SGEmpresaController
{
	protected $customResponse;

	protected $sgEmpresa;

	protected $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->sgEmpresa = new SGEmpresa();

		$this->validator = new Validator();
	}

	/*
	*	ENDPOINT GET list
	*/
	public function list(Request $request , Response  $response)
	{
		$getListEmpresa = $this->sgEmpresa->get();

		$this->customResponse->is200Response($response , $getListEmpresa);
	}

	/*
	*ENDPOINT UPDATED
	*/

	public function updated(Request $request , Response $response , $id)
	{
		$this->validator->validate($request , [

			"razon_social" => v::notEmpty(),
		    "nit" => v::notEmpty(),
		    "dv" => v::notEmpty(), 
			"codigo_soporte" => v::notEmpty(),
		    "direccion" => v::notEmpty(),
		    "prefijo" => v::notEmpty(),
		     
		]);

		if ($this->validator->failed()) {
			
			$responseMenssage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMenssage);

		}
		try
		{
			$this->sgEmpresa->where(["id_empresa" => $id])->update([

		    "razon_social" => CustomRequestHandler::getParam($request , "razon_social"),
		    "nit" => CustomRequestHandler::getParam($request , "nit"),
		    "dv" => CustomRequestHandler::getParam($request , "dv"),
		    "logo" => CustomRequestHandler::getParam($request , "logo"),
			"codigo_soporte" => CustomRequestHandler::getParam($request , "codigo_soporte"),
		    "direccion" => CustomRequestHandler::getParam($request , "direccion"),
		    "prefijo" => CustomRequestHandler::getParam($request , "prefijo"),
		    "html1" => CustomRequestHandler::getParam($request , "html1"),
		    "html2" => CustomRequestHandler::getParam($request , "html2"),
		    "html3" => CustomRequestHandler::getParam($request , "html3"),
		    "host" => CustomRequestHandler::getParam($request , "host"),
		    "mail_send" => CustomRequestHandler::getParam($request , "mail_send"),
		    "password" => CustomRequestHandler::getParam($request , "password"),
		    "port" => CustomRequestHandler::getParam($request , "port"),

		]);

		$responseMenssage = "actualizado";

		$this->customResponse->is200Response($response , $responseMenssage);
		
		}catch(Exception $e)
		{
			$this->customResponse->is400Response($response , $e->getMessage());
		}

		
	}


}


?>