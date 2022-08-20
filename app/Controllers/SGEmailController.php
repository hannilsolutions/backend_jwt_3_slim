<?php

namespace App\Controllers;

use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGGeneralidades;
use App\Models\SGEmpresa;
use App\Models\Usuario;
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

	protected $mail;

	protected $validator;

	protected $customResponse;

	protected $empresa;

	protected $usuario;


	public function __construct()
	{
		$this->mail = new PHPMailer(true);

		$this->validator = new Validator();

		$this->customResponse = new CustomResponse();

		$this->empresa 	= new SGEmpresa();

		$this->usuario = new Usuario();
	}

	public function sendMailFirma(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"id_user" => v::notEmpty(),
			"email"	 => v::notEmpty(),
			"user"  => v::notEmpty(),
			"id_permiso" => v::notEmpty(),
			"id_empresa" => v::notEmpty(),
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		#consultar plantilla para enviar
		$getPlantillaEmpresa = $this->plantilla(CustomRequestHandler::getParam($request , "id_empresa"));

		#generamos token
		$getToken = $this->generateTokenFirma();
		 
		#enviamos msm mail
		$getSendMail = $this->sendMail($getPlantillaEmpresa , $getToken , CustomRequestHandler::getParam($request , "email") , CustomRequestHandler::getParam($request , "user"));

		if (!$getSendMail) {
			
			$responseMessage = $getSendMail;

			return $this->customResponse->is400Response($response , $responseMessage);
		}
		#actualizar token en user agrega tiempo de expirar

		#enviar correo

		$responseMessage = "enviado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	public function generateTokenFirma()
	{
		$bytes = openssl_random_pseudo_bytes(3);
    	$hex   = bin2hex($bytes);

    	return $hex;
	}

	public function plantilla($idempresa)
	{
		$gethtml = $this->empresa->where("id_empresa" , "=" , $idempresa)->get();
		
		$plantilla = array();

		foreach($gethtml as $item)
		{
			$plantilla["html1"] 	= $item->html1;
			$plantilla["html2"] 	= $item->html2;
			$plantilla["html3"] 	= $item->html3;
			$plantilla["host"] 		= $item->host;
			$plantilla["mail_send"] = $item->mail_send;
			$plantilla["password"] 	= $item->password;
			$plantilla["port"]		= $item->port;
			$plantilla["razon_social"] = $item->razon_social;
		}

		return $plantilla;
	}

	public function sendMail($plantilla , $token , $destination , $name)
	{

		try{
				$this->mail->CharSet = "UTF-8";
			    $this->mail->SMTPDebug = 0;                      //Enable verbose debug output
			    $this->mail->isSMTP();                                            //Send using SMTP
			    $this->mail->Host       = $plantilla["host"];                     //Set the SMTP server to send through
				$this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
			    $this->mail->Username   = $plantilla["mail_send"];                     //SMTP username
			    $this->mail->Password   = $plantilla["password"];                               //SMTP password
			    $this->mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
			    $this->mail->Port       = $plantilla["port"];

			     //Recipients
			    $this->mail->setFrom($plantilla["mail_send"], $plantilla["razon_social"]);
			    $this->mail->addAddress($destination);

			    $this->mail->isHTML(true);
			    $this->mail->Subject = 'Código de confirmación HannilPro';
			    $this->mail->Body = $plantilla["html1"].$name.$plantilla["html2"].$token.$plantilla["html3"];

			    $this->mail->send();
			    echo $plantilla["mail_send"];
			    return true;
		}catch (Exception $e)
		{
				return $this->mail->ErrorInfo;
		}

	}


	
}
 
//https://www.cubicfactory.com/jseditor/
?>
