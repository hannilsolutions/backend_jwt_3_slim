<?php
namespace App\Controllers;

use App\Models\SGPermiso;
use App\Models\SGPermisoAptitud;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Dompdf\Dompdf;
class PdfEncuestaController{
    private  $dompdf;

    private $permiso;

    private $customResponse;

    private $permisoAptitud;

    private $validator;

    private $html;

    public function __construct()
    {
        $this->dompdf = new Dompdf();

        $this->permiso = new SGPermiso();

        $this->customResponse = new CustomResponse();

        $this->permisoAptitud = new SGPermisoAptitud();

        $this->validator = new Validator();
    }

    public function encuesta(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),
            "id_permiso_aptitud" => v::notEmpty()
        ]);

        if ($this->validator->failed())
         {
           $responseMessage = $this->validator->errors;

           return $this->customResponse->is400Response($response , $responseMessage);
        }
         $id = CustomRequestHandler::getParam($request , "id_permiso");
         $id_permiso_aptitud = CustomRequestHandler::getParam($request , "id_permiso_aptitud");
        try{
            $options = $this->dompdf->getOptions();
            $options->set(array('isRemoteEnabled' => true));
            $this->dompdf->setOptions($options);
            $this->first();
            //generando cabeceras;
            $this->cabeceras($id );
            /*$this->datos_vehiculo($id);
            $this->inspeccion($id);*/
            $this->permiso_aptitud($id_permiso_aptitud);
            $this->certifico();
            $this->last();
            $this->dompdf->loadHtml($this->html);
            $this->dompdf->setPaper("letter");
            $this->dompdf->render();
            $name = "Certificado_aptitud".$id;
            $pdf = $this->dompdf->stream($name , ["Attachment" => 1]);
    
            $this->customResponse->is200Pdf($response , $pdf);
    
            
            }catch (Dompdf\Exception\Exception $e) {
              $responseMessage = $e->getMessage();
    
              $this->customResponse->is400Response($response, $responseMessage);
            }catch(Exception $e)
            {
              $responseMessage = $e->getMessage();
        
              $this->customResponse->is400Response($response , $responseMessage);
            }
    }

    public function first()
    { 
      $this->html = '
      <html lang="en">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
          <style>
          .marca-agua {
            position: fixed;
            top: 40%;
            left: 30%;
            transform: rotate(-45deg);
            font-size: 40px;
            color: rgba(0, 0, 0, 0.1);
            opacity: 0.5;
            z-index: -1000;
        }
          </style>
      </head>
      <body>
          <div class="marca-agua">COMUNICAMOS + TELECOMUNICACIONES SAS <br>COMUNICAMOS + TELECOMUNICACIONES SAS<br>COMUNICAMOS + TELECOMUNICACIONES SAS</div>
      ';
    }

    private function cabeceras($id )
    {  
        $permiso = $this->permiso->selectRaw("han_sg_permiso_trabajo.id_permiso, 
        han_sg_permiso_trabajo.fecha_inicio , han_sg_permiso_trabajo.hora_inicio,
        han_sg_permiso_trabajo.fecha_cierre, han_sg_permiso_trabajo.hora_cierre,
        han_sg_permiso_trabajo.lugar_de_trabajo, 
        CASE WHEN han_sg_permiso_trabajo.estado = 1 THEN 'Abierto'
        WHEN han_sg_permiso_trabajo.estado = 2 THEN 'Cerrado'
        WHEN han_sg_permiso_trabajo.estado = 0 THEN 'Anulado' END as estado,
        han_sg_permiso_trabajo.prefijo, han_sg_permiso_trabajo.indicativo,
        han_sg_tipos_trabajo.nombre , count(han_sg_permisos_empleados.id_permisos_empleado) as cant")
        ->join("han_sg_tipos_trabajo" , "han_sg_tipos_trabajo.id_tipo" , "=" , "han_sg_permiso_trabajo.id_permiso_trabajo")
        ->join("han_sg_permisos_empleados" , "han_sg_permisos_empleados.id_permiso_trabajo" , "=" , "han_sg_permiso_trabajo.id_permiso")
        ->where(["id_permiso" => $id])->get();
        //$this->html .= $permiso[0];
        $this->html .= '
        <table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
          <tr>
          <td style="width:20%; text-align:center; padding:5px">
          <img src="https://comunicamosmas.com/wp-content/uploads/2024/09/LogoComu.png" width="80px"/> <!---->
          </td>
            <td>
             <div
             style="
            background: #00438A;
            color: #fff;
                padding: 5px;
                text-align: center !important;
              "> 
              <h3 style:"font-family:font-family: sans-serif;">CERTIFICADO DE REQUERIMIENTOS DE APTITUD FÍSICA PARA TRABAJO DE ALTO RIESGO</h3>
               </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div style="padding: 0 20px !important ;">
      <div style="text-align:center; background:#58D68D;color:#fff;">
      <h4 style:"font-family:font-family: sans-serif; ">Permiso # '.$permiso[0]->prefijo.' '.$permiso[0]->indicativo.' / Estado : '.$permiso[0]->estado.'</h4>
      </div>
      </div>
      <!--iniciar con encabezado-->
      <div style="padding:0 20px 0 20px ; ">
      <table style="width: 100%" cellspacing="0" cellpadding="0" border="1px">
      <tbody>
        <tr>
          <td width="33%">
            <b>Fecha: </b>'.$permiso[0]->fecha_inicio.'
          </td>
          <td width="33%">
          <b>Hora Inicio: </b>'.$permiso[0]->hora_inicio.'
          </td>
          <td width="33%">
          <b>Tipo de trabajo: </b>'.$permiso[0]->nombre.'
          </td>
        </tr>
        <tr>
           <td colspan="2"><b>N° de personas que ejecutan la labor: </b>'.$permiso[0]->cant.'</td>
           <td><b>Lugar de trabajo: </b>'.$permiso[0]->lugar_de_trabajo.' </td>
        </tr> 
        <tr>
          <td colspan=3 style="text-align:center;">
              <p style="font-size:10px">
              Cuestionario para certificación de aptitud física de empleados autorizados para realizar trabajo en Alturas. Con el fin de asegurar que aquellos trabajadores que han sido autorizados (CONCEPTO MÉDICO APTOS) Por el personal profesional en salud, para realizar trabajos de alto riesgo, se encuentran en óptimas condiciones, el personal responsable de la labor deberá diligenciar este cuestionario diariamente.
              </p>
          </td>
        </tr>
      </tbody>
    </table>
    
    
      </div>
        
      ';
      //$html .= $this->subtitulo("CERTIFICADO DE REQUERIMIENTOS DE APTITUD FÍSICA PARA TRABAJO DE ALTO RIESGO");
      $this->html .= $html;

         
    }

    private function subtitulo($name)
    {
       
      return '<div style="text-align:center;padding:0 20px;">
      <div style="background:#58C3D6><strong style="color:white">Certificado realizado por: '.$name.'<strong></div>
      </div>';
    }

    private function permiso_aptitud($id_permiso_aptitud)
    {
      $aptitud = $this->permisoAptitud->selectRaw("han_sg_permiso_aptitud.json , users.user")
                          ->join("users" , "users.id" , "=" , "han_sg_permiso_aptitud.id_user")
                          ->where(["han_sg_permiso_aptitud.id_permiso_aptitud" => $id_permiso_aptitud])
                          ->get();
      
      $name;
      $json;
      foreach($aptitud as $item)
      {
        $json = json_decode($item->json);
        $name = $item->user;
      }
      $this->html .= $this->subtitulo($name);
      $html = '<div style="padding: 0 20px">';
        foreach($json as $pr)
        {
          $html .= '<hr> <table style="width: 100%; align-items:center" cellspacing="0" cellpadding="0" border="1px"> 
          <thead><tr><td colspan="3" style="background-color:#FFC300; color:white">'.$pr->titulo.'</td></tr>
                <tr>
                  <td>Requerimiento</td>
                  <td>Respuesta</td>
                  <td>Observación</td></tr></thead><tbody>
                ';
                  foreach($pr->preguntas as $item)
                  {
                    $observacion = $item->Observacion ? $item->Observacion : null;
                    $html .= '
                      <tr>
                      <td style="font-size:10px;">'.$item->title.'</td>
                      <td style="font-size:10px;">'.$item->respuesta.'</td>
                      <td style="font-size:10px;">'.$observacion.'</td>
                      </tr>      
                    ';
                  }
            $html .= '</tbody></table>';
        }

      $html .= '</div>';

      $this->html .= $html;
    }

    private function certifico()
    {
      $this->html .= '<br>
        <div style="padding: 0 20px">
        <table style="width: 100%; align-items:center" cellspacing="0" cellpadding="0" border="1px"><tr>
        <td style="text-align:center;">
            <p style="font-size:10px">
            CERTIFICO QUE LA INFORMACIÓN DE ESTE REPORTE ES VERIDICA Y QUE EN ESTE MOMENTO NO PADEZCO NINGUNA CONDICIÓN FISICA CONOCIDA POR MI QUE PUEDA AFECTAR MI DESEMPEÑO EN LA TAREA DE ALTO RIESGO EN LA CUAL ESTARE INVOLUCRADO </p>
        </td>
      </tr></table>
        </div>
      
      ';
    }

    private function last()
    {
      $this->html .= '</body></html>';
    }
}