<?php

namespace App\Controllers;

use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGProcedimiento;
use App\Models\SGPermiso;
use App\Models\SGPermisosPeligros;
use App\Models\SGDetalleFirmas;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Dompdf\Dompdf;

class generarPdfController
{
   private  $dompdf;

   private $permiso;

   private  $permisoEmpleado;

   private $listEmpleados;

   private $empleadoGeneralidades;

   private $permisoPeligros;

   private $detalle_firmas;

   private $customResponse;

   private $procedimiento;

   private $html;

   public function __construct()
   {
    $this->dompdf = new Dompdf();

    $this->permisoEmpleado = new SGPermisoEmpleado();

    $this->permiso = new SGPermiso();

    $this->empleadoGeneralidades = new SGEmpleadoGeneralidades();

    $this->permisoPeligros = new SGPermisosPeligros();

    $this->detalle_firmas = new SGDetalleFirmas();

    $this->customResponse = new CustomResponse();

    $this->procedimiento = new SGProcedimiento();
   }

    public function permisoAlturas(Request $request , Response $response , $id)    
    {
       try{
        $options = $this->dompdf->getOptions();
        $options->set(array('isRemoteEnabled' => true));
        $this->dompdf->setOptions($options);
        //generando cabeceras;
        $this->first();
        $this->cabeceras($id);
        $this->empleados($id);
        $this->genealidadades($id);
        //peligros
        $this->peligros($id);
        //firmas
        $this->grupo_aprobacion($id);
        $this->last();
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper("letter");
        $this->dompdf->render();
        $name = "Permiso_trabajo_".$id["id"];
        $pdf = $this->dompdf->stream($name , ["Attachment" => 1]);

        $this->customResponse->is200Pdf($response , $pdf);

        } catch (Dompdf\Exception\Exception $e) {
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
          <div class="marca-agua">CCOMUNICAMOS + TELECOMUNICACIONES SAS<br>COMUNICAMOS + TELECOMUNICACIONES SAS<br>COMUNICAMOS + TELECOMUNICACIONES SAS</div>
      ';
    }

    private function last()
    {
      $this->html .= '</body></html>';
    }

    private function cabeceras($id)
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
        $html = '
        <table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
        <tbody>
          <tr>
          <td style="width:20%; text-align:center; padding:5px">
          <img src="https://comunicamosmas.com/wp-content/uploads/2024/09/LogoComu.png" width="80px"/>
          </td>
            <td>
             <div
             style="
            background: #00438A;
            color: #fff;
                padding: 5px;
                text-align: center !important;
              "> 
              <h3 style:"font-family:font-family: sans-serif;">PERMISOS DE TRABAJO EN ALTURAS</h3>
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
          <td colspan="3" style="text-align:center;">
          <p style="font-size:10px">1. Si las condiciones iniciales bajo las cuales se autoriza este permiso de trabajo
          cambian, se debe suspender el trabajo y realizar las acciones correctivas pertinentes, para poder continuar con la labor.</p></td>
        </tr>
        <tr>
           <td colspan="3" style="text-align:center">
            <p style="font-size:10px">IMPORTANTE !!HACER MANTENIMIENTO DE ELEMENTOS DE PROTECCIÓN Y EQUIPOS CONTRA CAÍDA EN ALTURAS CADA 8 DÍAS</p>
           </td>
        </tr>
        
      </tbody>
    </table>
    
    
      </div>
        
      '.$this->subtitulo("I. PERMISO CONCEDIDO A").'
      ';
      $this->html .= $html;

        //return $html2;
//#00438A
    }
    function subtitulo($name)
    {
      /*return '<div style="padding: 0 20px !important ; margin:0; background:red">
      <div style="text-align:center; background:#58C3D6;color:#fff; margin:0;padding:0">
      <h4 style:"font-family:font-family: sans-serif; ">'.$name.'</h4>
      </div>
      </div>';*/
      return '<div style="text-align:center;padding:0 20px;">
      <div style="background:#58C3D6><b style="font-size:10px">'.$name.'</b></div>
      </div>';
    }

    function empleados($id)
    {
      
      /**select users.user , datos_personales.documento , datos_personales.cargo ,
                case 
              WHEN han_sg_permisos_empleados.firma ='' or han_sg_permisos_empleados.firma is null THEN 'No firmado'
              else 'Firmado' end as firmas
              from han_sg_permisos_empleados
              inner join users on users.id = han_sg_permisos_empleados.id_user
              left join datos_personales on datos_personales.id_user = users.id
            where  han_sg_permisos_empleados.id_permiso_trabajo = 4; */
      $list  = $this->permisoEmpleado->selectRaw("han_sg_permisos_empleados.id_user, users.user , datos_personales.documento , datos_personales.cargo ,
      case 
      WHEN han_sg_permisos_empleados.firma ='' or han_sg_permisos_empleados.firma is null THEN 'No firmado'
      else 'Firmado' end as firmas")
              ->join("users" , "users.id" , "=" , "han_sg_permisos_empleados.id_user")
              ->leftjoin("datos_personales" , "datos_personales.id_user", "=" ,"users.id")
              ->where(["han_sg_permisos_empleados.id_permiso_trabajo" => $id])->get();
  
      $empleados = "";
      $conteo =1;
      foreach($list as $item)
      {
        $empleados = $empleados.'<tr style="font-family: Arial; font-size: 10px;"><td>T'.$conteo.'</td><td>'.$item->user.'</td><td>'.$item->documento.'</td><td>'.$item->cargo.'</td><td>'.$item->firmas.'</td></tr>';

        $conteo++;
      }
   
      $this->listEmpleados = $list;
      $html = '<div style="padding:0px 20px 0 20px;margin:0;"><table style="width: 100%" cellspacing="0" cellpadding="0" border="1px">
      <thead>
      <tr>
      <th>Item</th>
      <th>Nombre</th>
      <th>N° cédula</th>
      <th>Cargo</th>
      <th>Estado</th>
      </tr>
      </thead>
      '.$empleados.'
       
    </table></div>';

    $this->html .= $html;
       
    }


    //generalidades
    function genealidadades($permiso)
    {
      $html = $this->subtitulo("II. LISTAS DE CHEQUEO");
      $generalidad = ["EPP","EPCC","Herramientas"];
      $count = 1;
      foreach($this->listEmpleados as $item){
        //$html .= '<div style="padding:0 20px; background:green "> Trabajador'.$count;
        $html .= '<div style="padding:0 20px;"><table style="width: 100%" cellspacing="0" cellpadding="0" border="1px">  
                    <tr>
                      <td><strong>Trabajador'.$count.'</strong></td>
                    </tr>';
          foreach($generalidad as $gene)
          {
            $html .= $this->createGeneralidad($item->id_user , $gene , $permiso);
            $html .= $this->generateProcedimiento($item->id_user , $permiso);
          }
        $html .= "</table></div>";
          $count++;
      }

      $this->html .= $html;
    }

    private function generateProcedimiento($id_user , $id_permiso)
    {
      $procedimiento = $this->procedimiento->where(["id_user" => $id_user])->where(["id_permiso" => $id_permiso])->get();

      if($procedimiento->count() > 0)
      {
        $json ="";
      foreach($procedimiento as $item)
      {
        if(isset($item->procedimiento))
        {
          $json = json_decode($item->procedimiento);
        }
      }

        $html = '<tr>
                    <td style="font-size:10px"><strong>Procedimientos</strong></td>
                  </tr> <tr><td>';
        foreach($json as $js)
        {
          $html .= htmlspecialchars($js, ENT_QUOTES, 'UTF-8').' , ';
        }

        return $html;

      }

    }
    function createGeneralidad($user, $gene , $permiso)
    {
      /**SELECT emp.inspeccion,ge.nombre FROM han_sg_empleados_generalidades emp 
       * inner join han_sg_generalidades ge on emp.generalidades_id = ge.id_generalidades
       *  where emp.permiso_id = 4 and emp.empleado_id = 30 and ge.tipo = "EPP" and emp.active = "Y" */
      $empleado_generalidad  = $this->empleadoGeneralidades->selectRaw("han_sg_empleados_generalidades.inspeccion ,han_sg_generalidades.nombre")
                            ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
                            ->where(["han_sg_empleados_generalidades.permiso_id" => $permiso])
                            ->where(["han_sg_empleados_generalidades.empleado_id" => $user])
                            ->where(["han_sg_generalidades.tipo" => $gene])
                            ->get();
      //$html = '<div style="background:red;"><h3>'.$gene.'</h3>';
      $html = '<tr>
                  <td style="font-size:10px"> <strong>'.$gene.'</strong></td> </tr> <tr><td>';
      
      if($empleado_generalidad->count() > 0)
      {
        foreach($empleado_generalidad as $item)
        {
          $color = '#BBBBBB';
          if($item->inspeccion =='Bueno')
          { 
            $color ="#DAF7A6";
          }elseif($item->inspeccion=='Malo')
          {
            $color = "#FCB0A2";
          }
          $html .= '<div style="display:inline-block; padding:2px;font-size:10px; background:'.$color.'; border-radius:25% 10%;"><strong>'.$item->nombre.':</strong> '.$item->inspeccion.'</div>';
        }
      }else{
        $html .= '<div>Sin registros</div>';
      }

      $html .="</td></tr>";

      return $html;

      
    }

    /**Listas de peligros 
     * @param idPermiso
     * SELECT hsp.nombre , hsp.consecuencias , GROUP_CONCAT(hsc.nombre SEPARATOR ',') as controles  FROM han_sg_permisos_peligros hspp 
  INNER JOIN han_sg_peligros hsp on hsp.id_peligro  = hspp.peligro_id 
  LEFT JOIN han_sg_controles hsc ON hsc.id_peligro  = hsp.id_peligro 
  WHERE hspp.permiso_id =2
  GROUP BY hspp.permiso_peligro_id 
    */

    public function peligros($permiso)
    {
       
      $html = $this->subtitulo("III. ANALISIS SEGURO DE TRABAJO - AST ");
      $peligros = $this->permisoPeligros->selectRaw(
                      "han_sg_peligros.nombre , han_sg_peligros.consecuencias , GROUP_CONCAT(han_sg_controles.nombre SEPARATOR ',') as controles"
                    )->join("han_sg_peligros" , "han_sg_peligros.id_peligro" , "=" , "han_sg_permisos_peligros.peligro_id")
                    ->leftjoin("han_sg_controles" , "han_sg_controles.id_peligro" , "=" , "han_sg_peligros.id_peligro")
                    ->where("han_sg_permisos_peligros.permiso_id", "=" , $permiso)
                    ->groupBy("han_sg_permisos_peligros.permiso_peligro_id")->get();
        $html .='<div style="padding: 0 20px;">
          <table style="width: 100%" cellspacing="0" cellpadding="0" border="1px">
          <tr>
          <th>Peligro</th>
          <th>Consecuencias</th>
          <th>Controles</th>
          </tr>
        ';
        foreach($peligros as $item)
        {
          $html .='<tr>
          <td>'.$item->nombre.'</td>
          <td>'.$item->consecuencias.'</td>
          <td>'.$item->controles.'</td>
          </tr>
          ';
        }
        $html .= "</table></div>";

      $this->html .= $html;
    }

    /**
     SELECT hsdf.id_firma , hsf.cargo , dp.documento , dp.cargo  FROM han_sg_detalle_firma hsdf 
INNER JOIN han_sg_firmas hsf ON hsf.id  = hsdf.id_firma 
INNER JOIN datos_personales dp ON dp.id_user = hsf.id_user 
WHERE hsdf.id_permiso = 2
     */
    public function grupo_aprobacion($id)
    {
        $html = $this->subtitulo("GRUPO DE ANALISIS Y APROBACIÓN");
        $html .= '<div style="padding: 0 20px;">
          <table style="width: 100%" cellspacing="0" cellpadding="0" border="1px">
          <tr>
          <td colspan="5">Acepto este análisis y considero que la tarea/actividad/operación se puede ejecutar cumpliendo con los controles existentes y recomendaciones descritos en este
           PTSA.(Permiso de trabajo seguro en alturas)</td>
           </tr>
           <tr>
                <th colspan="2">Nombres y apellidos</th>
                <th>No Identificación</th>
                <th>Cargo</th>
                <th>Firma</th>
           </tr>
        ';
        $firmas =  $this->detalle_firmas->selectRaw("han_sg_detalle_firma.id_firma , han_sg_detalle_firma.url_firma,
        han_sg_firmas.cargo as car , 
        datos_personales.documento  , 
        datos_personales.cargo ,
        users.user")
                  ->join("han_sg_firmas" , "han_sg_firmas.id" , "=" , "han_sg_detalle_firma.id_firma")
                  ->join("datos_personales" , "datos_personales.id_user" , "=" , "han_sg_firmas.id_user")
                  ->join("users" , "users.id" , "=" , "datos_personales.id_user")
                  ->where("han_sg_detalle_firma.id_permiso" , "=" , $id)->get();

        foreach($firmas as $item)
        {
            if($item->car =="revisostt"){$tipo = "REVISIÓN SST";}else{ $tipo = "REVISO";}
            if(empty($item->url_firma)){$firma="no firmado";}else{$firma="firmado";}
            $html .= '<tr>
                <td>'.$tipo.'</td>
                <td>'.$item->user.'</td>
                <td>'.$item->documento.'</td>
                <td>'.$item->cargo.'</td>
                <td>'.$firma.'</td>
            </tr>';
        }
        $html .= '<tr>
          <td colspan="5">AST PERIFERICO:Revisión de las condiciones en el sitio de trabajo para identificar aquellos peligros originarios por el medio ambiente ,
          activida simultanea u otras del entorno NO identificadas en el AST de la tarea. Igualmente se debe establecer las medidas de control requeridas
          </td></tr></table></div>';

        $this->html .= $html;
    }

   

  
}

?>