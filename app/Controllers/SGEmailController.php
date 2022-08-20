<?php

namespace App\Controllers;

use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGGeneralidades;
use App\Models\SGEmpresa;
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

		$this->empresa 	= new SGEmpresa();
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
		$getPlantillaEmpresa = $this->plantilla(CustomRequestHandler::getParam($request , "id_empresa"));

		#actualizar token en user agrega tiempo de expirar

		#enviar correo
	}

	public function generateTokenFirma()
	{
		$bytes = openssl_random_pseudo_bytes(3);
    	$hex   = bin2hex($bytes);

    	return $hex;
	}

	public function plantilla($idempresa)
	{
		$gethtml = $this->empresa->selectRaw("html1 , html2 , html3")->where("id_empresa" , "=" , $idempresa)->get();
		
		$plantilla = array();

		foreach($plantilla as $item)
		{
			$plantilla["html1"] = $item->html1;
			$plantilla["html2"] = $item->html2;
			$plantilla["html3"] = $item->html3;
		}

		return $plantilla;
	}

	public function sendMail($plantilla)
	{
		try{
			    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
			    $mail->isSMTP();                                            //Send using SMTP
			    $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
				$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
			    $mail->Username   = 'user@example.com';                     //SMTP username
			    $mail->Password   = 'secret';                               //SMTP password
			    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
			    $mail->Port       = 465;                                    

		}catch
		{

		}

	}


	
}
 
//https://www.cubicfactory.com/jseditor/
?>
