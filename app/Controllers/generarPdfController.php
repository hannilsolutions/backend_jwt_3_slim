<?php

namespace App\Controllers;

use App\Models\SGPermisoEmpleado;
use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGPermiso;
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

   private $html;

   public function __construct()
   {
    $this->dompdf = new Dompdf();

    $this->permisoEmpleado = new SGPermisoEmpleado();

    $this->permiso = new SGPermiso();

    $this->empleadoGeneralidades = new SGEmpleadoGeneralidades();
   }

    public function permisoAlturas(Request $request , Response $reponse , $id)
    
    {
        $options = $this->dompdf->getOptions();
        $options->set(array('isRemoteEnabled' => true));
        $this->dompdf->setOptions($options);
        //generando cabeceras;
        $this->cabeceras($id);
        $this->empleados($id);
        $this->genealidadades($id);
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper("letter");
        $this->dompdf->render();

        $this->dompdf->stream("invoce.pdf" , ["Attachment" => 1]);
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
          <img src="https://apps.internetinalambrico.com.co/Files/In.jpg" width="80px"/>
          </td>
            <td>
             <div
             style="
            background: #00438A;
            color: #fff;
                padding: 5px;
                text-align: center !important;
                border-radius : 10% 30% 50% 70%;
              "> 
              <h3 style:"font-family:font-family: sans-serif;">PERMISOS DE TRABAJO EN ALTURAS</h3>
               </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div style="padding: 0 20px !important ;">
      <div style="border-radius: 10px 100px / 120px;text-align:center; background:#58D68D;color:#fff;">
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
      <th>Firma</th>
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
          }
        $html .= "</table></div>";
          $count++;
      }

      $this->html .= $html;
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

   

  
}

?>