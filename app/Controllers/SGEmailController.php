<?php

namespace App\Controllers;

use App\Controllers\WsSendMessageController;
use App\Models\DatosPersonales;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGGeneralidades;
use App\Models\SGEmpresa;
use App\Models\Usuario;
use App\Models\SGPermisoEmpleado;
use App\Models\SGPermisosPeligros;
use App\Models\SGPermisoVehiculo;
use App\Models\SGVehiculosGeneralidades;

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

	protected $wsSendMessage;

    protected $datosPersonales;

	protected $permisosEmpleados;

	protected $generalidadesEmpleados;

	protected $generalidades;

	protected $permisoPeligros;

	protected $permisoVehiculo;

	protected $vehiculoGeneralidades;


	public function __construct()
	{
		$this->mail = new PHPMailer(true);

		$this->validator = new Validator();

		$this->customResponse = new CustomResponse();

		$this->empresa 	= new SGEmpresa();

		$this->usuario = new Usuario();

		$this->wsSendMessage = new WsSendMessageController();

        $this->datosPersonales = new DatosPersonales();

		$this->permisosEmpleados = new SGPermisoEmpleado();

		$this->generalidadesEmpleados = new SGEmpleadoGeneralidades();

		$this->generalidades = new SGGeneralidades();

		$this->permisoPeligros = new SGPermisosPeligros();

		$this->permisoVehiculo = new SGPermisoVehiculo();

		$this->vehiculoGeneralidades = new SGVehiculosGeneralidades();
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

		/**
		 * Validar permisos
		 */
		$errors = $this->valid_permiso_trabajo(CustomRequestHandler::getParam($request , "id_permiso") , CustomRequestHandler::getParam($request , "id_user"));
		if($errors)
		{
			$responseMessage = $errors;

			return $this->customResponse->is422Response($response , $responseMessage);

		}

		#consultar plantilla para enviar
		$getPlantillaEmpresa = $this->plantilla(CustomRequestHandler::getParam($request , "id_empresa"));

		#generamos token
		$getToken = $this->generateTokenFirma();
		 
		#enviamos msm mail
		$getSendMail = $this->sendFunctionMail($getPlantillaEmpresa , $getToken , CustomRequestHandler::getParam($request , "email") , CustomRequestHandler::getParam($request , "user"));

		if (!$getSendMail) {
			
			$responseMessage = "error enviando correo";

			return $this->customResponse->is400Response($response , $responseMessage);
		}
		#actualizar token en user agrega tiempo de expirar
		$setTokenUser = $this->updatedTokenUsuario(CustomRequestHandler::getParam($request , "id_user") , $getToken);
		#enviar por whatsapp
		$id = CustomRequestHandler::getParam($request , "id_user");
		$datos_personales =  $this->datosPersonales->where("id_user" , "=" , $id)->get();
        //$whatsapp = "";
        foreach($datos_personales as $item)
        {
            $whatsapp = $item->celular;
        }
        if(!empty($whatsapp) && mb_strlen($whatsapp, "UTF-8") == 10 && ctype_digit($whatsapp))
        {
            $msm = "Su código para firmar es ".$getToken;
            try {
				$this->wsSendMessage->send_text($whatsapp , $msm);
			} catch (Exception $e) {
				error_log("Error al enviar mensaje: " . $e->getMessage());
			}
        }

		$responseMessage = "enviado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/**Validate permiso de trabajo por usuario */

	private function valid_permiso_trabajo($permiso , $user)
	{
		//buscar integrante del permiso
		$integrante = $this->permisosEmpleados->where(["id_permiso_trabajo"=>$permiso])->where(["id_user"=>$user])->count();
		$errors = [];
		if($integrante > 0)
		{			
			//REALIZAMOS UN DISCT DE TODOS LAS generalidades en estado 1
			$generalidad = $this->generalidades
								->selectRaw("DISTINCT han_sg_generalidades.tipo")
								->where(["estado"=>1])
								->get();
			foreach($generalidad as $item)
			{
				 //consultar generalidad exceptuando camioneta
				 if(empty($item->tipo) || $item->tipo ==="CAMIONETA" || $item->tipo ==="MOTOCICLETA")
				 {
					continue;
				 }else{
					$query = $this->generalidadesEmpleados
							->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
							->where(["han_sg_generalidades.tipo" => $item->tipo])
							->where(["han_sg_empleados_generalidades.empleado_id"=>$user])
							->where(["han_sg_empleados_generalidades.permiso_id" => $permiso])
							->get();

					if($query->count() == 0)
					{
						$errors[] = array("tipo" => $item->tipo , "value" => "Sin datos");
					}else{

						$cantidad_generalidades = $query->count();
						$cantidad_nulos = 0;
						foreach($query as $a)
						{
							if(empty($a->inspeccion))
							{
								$cantidad_nulos += 1;
							}
						}

						if($cantidad_generalidades == $cantidad_nulos)
						{
							$errors[] = array("tipo" => $item->tipo , "value" => "Ninguno inspeccionado");
						}
					}
				 }
						
			}
			//buscar si agrego peligros siempre y cuando
			$peligros = $this->permisoPeligros->where(["permiso_id"=>$permiso])->get();
			if($peligros->count() == 0)
			{
				$errors[] = array("tipo" => "Peligros" , "value" => "sin datos");
			}

			//buscar si agrego vehiculo	

			$vehiculo = $this->permisoVehiculo->where(["permiso_id"=>$permiso])->get();
			if($vehiculo->count() > 0)
			{
				$vehiculo_id = 0;
				$inspeccion = [];

				foreach($vehiculo as $id)
				{
					$vehiculo_id = $id->permiso_vehiculo_id;
				}
				
				$query = $this->vehiculoGeneralidades->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_vehiculos_generalidades.generalidades_id")
								->where(["permiso_vehiculo_id"=>$vehiculo_id])
									->get();
				
				foreach($query as $item)
				{
					if(empty($item->inspeccion))
					{
						$inspeccion[] = $item->nombre;
					}
				}

				if(!empty($inspeccion))
				{
					$toString = '('. implode(',' , $inspeccion) . ',)';

					$errors[] = array("tipo" => "Inspección" , "value" => $toString ) ;
				}
					
				 
			}
		}

		return $errors;
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
			    $this->mail->SMTPDebug = 2;                      //Enable verbose debug output
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

			    return true;

		}catch (Exception $e)
		{
				 

				return $this->mail->ErrorInfo;

		}

	}

	public function sendFunctionMail($plantilla , $token , $destination , $name)
	{
		//// Para enviar un correo HTML, debe establecerse la cabecera Content-type
		$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
		$cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		// Cabeceras adicionales
		$cabeceras .= 'From: HannilPro <sistemas@hannilsolutions.com>' . "\r\n";

		// Enviarlo

		$subject = 'Código de confirmación';
		$msm = $plantilla["html1"].$name.$plantilla["html2"].$token.$plantilla["html3"];
		$msm = wordwrap($msm , 70);

		if(!mail($destination ,$subject , $msm , $cabeceras))
		{
			return false;
		}

		return true;

	
	}

	public function updatedTokenUsuario($id_user , $token)
	{
		try{
			$this->usuario->where("id" , "=" , $id_user)->update([
				"token_pw" => $token,
				"fecha_caducidad" => date("Y-m-d H:i:s")
			]);
			return true;
		}catch(Exception $e)
		{
			return false;
		}

		
	} 
	/*
	*ENPOINT POST validar token creado dos minutos
	*/
	public function validateToken(Request $request , Response $response )
	{
		$this->validator->validate($request , [
			"token" => v::notEmpty(),
			"id_user" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		#traemos info de cliente
		$getInfoToken = $this->findUserById(CustomRequestHandler::getParam($request , "id_user"));

		#fecha de expiracion
		$fecha_caducidad = date("Y-m-d H:i:s");

		#reduccion de fechacliente con fecha caducidad
		$residuoFechas = strtotime($fecha_caducidad) - strtotime($getInfoToken["fecha_caducidad"]);

		#validamos el valor inferior a 120 segundo
		if($residuoFechas > 120)
		{
			$responseMessage = "Token Expired";

			return $this->customResponse->is400Response($response , $responseMessage);
		}
		#validamos si el token es igual al enviado
		$token_pw = CustomRequestHandler::getParam($request , "token");

		if($getInfoToken["token_pw"] != $token_pw)
		{
			$responseMessage = "token errado";

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$responseMessage = "validado";

		$this->customResponse->is200Response($response  , $responseMessage);

	}

	public function findUserById($id)
	{
		$user = array();

		$getUser = $this->usuario->selectRaw("token_pw , fecha_caducidad")
											->where("id" , "=" , $id)
											->get();
		foreach($getUser as $item)
		{
			$user["token_pw"] = $item->token_pw;

			$user["fecha_caducidad"] = $item->fecha_caducidad;
		}

		return $user;
	}


	
}
 
//https://www.cubicfactory.com/jseditor/
?>
