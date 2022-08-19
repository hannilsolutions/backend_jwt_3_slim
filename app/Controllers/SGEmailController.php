<?php

namespace App\Controllers;

use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class SGEmailController
{

	protected $email;

	protected $validator;

	protected $customResponse;


	public function __construct()
	{
		$this->email = new PHPMailer(true);

		$this->validator = new Validator();

		$this->customResponse = new CustomResponse();
	}

	public function sendMailFirma(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"id_user" => v::notEmpty(),
			"id_permiso" => v::notEmpty(),
			"id_empresa" => v::notEmpty(),
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		#consultar plantilla para enviar
		$getPlantillaEmpresa = "";

		#actualizar token en user agrega tiempo de expirar

		#enviar correo
	}

	public function generateTokenFirma()
	{
		$bytes = openssl_random_pseudo_bytes(3);
    	$hex   = bin2hex($bytes);

    	return $hex;
	}


	
}
 
//https://www.cubicfactory.com/jseditor/
?>
