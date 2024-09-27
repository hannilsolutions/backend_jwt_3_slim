<?php

namespace App\Controllers;

use App\Controllers\WsSendMessageController;
use App\Models\SGPermisoAptitud;
use App\Models\SGEmpleadoAptitud;
use App\Models\SGGeneralidades;
use App\Models\SGConfiguration;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPermisoAptitudController
{

    protected  $customResponse;

    protected  $permisoAptitud;

    protected $empleadoAptitud;

    protected $generalidades;

    protected  $validator;

    protected $wsMessageController;

    protected $configuration;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->permisoAptitud = new SGPermisoAptitud();

         $this->empleadoAptitud = new SGEmpleadoAptitud();

         $this->generalidades = new SGGeneralidades();

         $this->validator = new Validator();

         $this->wsMessageController = new WsSendMessageController();

         $this->configuration = new SGConfiguration();
    }

    public function create(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "id_permiso"=>v::notEmpty(),
           "id_user"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");
        $id_user = CustomRequestHandler::getParam($request , "id_user");


        $permisoAptitud  = $this->permisoAptitud->where(["id_permiso" => $id_permiso])->where(["id_user" => $id_user])->get();
        if($permisoAptitud->count() == 0)
        {
            $permiso = $this->permisoAptitud->create([
                "id_user" => $id_user,
                "id_permiso" => $id_permiso,
                "estado" => "CREATE"
            ]);

            $generalidades = $this->generalidades->where(["item"=>"Preguntas"])->where(["estado"=>1])->get();

            if($generalidades->count() > 0)
            {
                foreach($generalidades as $item)
                {
                    $this->empleadoAptitud->create([
                        
                        "id_generalidades" => $item->id_generalidades,
                        "id_permiso_aptitud" => $permiso->id          
                    ]);
                }
            }
                

            return $this->customResponse->is200Response($response,$permiso);

        }    

    }

    public function find_by_permiso(Request $request , Response $response , $id)
    {
        $list = $this->permisoAptitud->selectRaw("han_sg_permiso_aptitud.id_permiso_aptitud , han_sg_permiso_aptitud.estado, users.user")->join("users" , "users.id" , "=" , "han_sg_permiso_aptitud.id_user")->where(["id_permiso" => $id])->get();

        return $this->customResponse->is200Response($response , $list);
    }

    public function find_by_permiso_and_empleado(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id_permiso"=>v::notEmpty(),
            "id_user"=>v::notEmpty()
         ]);

         if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $id_user = CustomRequestHandler::getParam($request , "id_user");
        
        $query = $this->permisoAptitud->where(["id_permiso" => $id_permiso])->where(["id_user"=>$id_user])->get();
        $permiso_aptitud = new \stdClass();
        if($query->count() > 0)
        {
            $id_permiso_aptitud = 0;

            
            $permiso_aptitud->count = true;

            $permiso_aptitud->permiso_aptitud = $query[0];
            foreach($query as $item)
            {
                $id_permiso_aptitud = $item->id_permiso_aptitud;
            }

            $query_empleado_aptitud = $this->empleadoAptitud->selectRaw("han_sg_empleado_aptitud.id_empleado_aptitud , han_sg_empleado_aptitud.id_generalidades 
            , han_sg_empleado_aptitud.respuesta , han_sg_generalidades.nombre ")
                            ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleado_aptitud.id_generalidades")
                            ->where(["han_sg_empleado_aptitud.id_permiso_aptitud" => $id_permiso_aptitud])
                            ->get();
            $permiso_aptitud->empleado_aptitud = $query_empleado_aptitud;

            return $this->customResponse->is200Response($response , $permiso_aptitud);
        }else{
            $permiso_aptitud->count = false;

            return $this->customResponse->is200Response($response , $permiso_aptitud);
        }
    }

    public function validate_aptitud(Request $request , Response $response , $id)
    {
        $permiso_aptitud = $this->permisoAptitud->where(["id_permiso_aptitud" => $id])->get();
        if($permiso_aptitud->count() > 0)
        {
            $aptitud = new \stdClass();

            $aptitud = $permiso_aptitud[0]; 

            if($aptitud->estado == "CREATE")
            {
                $preguntas = $this->empleadoAptitud->where(["id_permiso_aptitud" => $aptitud->id_permiso_aptitud])->get();

                $errors = false;

                $status = "";

                foreach($preguntas as $item)
                {
                    if($item->respuesta == "Si")
                    {
                        $errors = true;
                        break;
                    }
                }

                if($errors){
                    $status = "PENDING";
                    //$configuration_html = $this->configuration->where(["code"=> "html_condicion_actual"])->get();
                    $configuration_person = $this->configuration->where(["code" => "reporte_aprobacion"])->get();
                    $json_info = "";
                    foreach($configuration_person as $item)
                    {
                        if(!empty($item->value))
                        {
                            $json_info = json_decode($item->value);
                        }
                    }
                    $msm = "Hola ".$json_info->nombre.", el permiso N# ".$aptitud->id_permiso." presenta anomalias en sus respuestas de ***Estado actual***";
                    $this->wsMessageController->send_text($json_info->celular , $msm);


                }else{
                    $status = "APROVED";
                }

                 $this->permisoAptitud->where(["id_permiso_aptitud" => $aptitud->id_permiso_aptitud])->update([
                    "estado" => $status
                ]);

                return $this->customResponse->is200Response($response , $status);
            }
        }else{
            $responseMessage = "permiso ".$id["id"]. "  not found";
            return $this->customResponse->is400Response($response , $responseMessage);
        }
    }

     

    public function update_status_permiso_aptitud(Request $request ,  Response $response , $id)
    {
        $this->permisoAptitud->where(["id_permiso_aptitud" => $id])->update([
            "estado" => "APROVED"
        ]);

        return $this->customResponse->is200Response($response , "Actualizado");
    }


    /**send mail notification */

    private function sendMail($plantilla , $destination)
	{
		//// Para enviar un correo HTML, debe establecerse la cabecera Content-type
		$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
		$cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		// Cabeceras adicionales
		$cabeceras .= 'From: HannilPro <sistemas@hannilsolutions.com>' . "\r\n";

		// Enviarlo

		$subject = 'Condición Actual';
		$msm = $plantilla;
		$msm = wordwrap($msm , 70);

		if(!mail($destination ,$subject , $msm , $cabeceras))
		{
			return false;
		}

		return true;

	
	}

}

?>