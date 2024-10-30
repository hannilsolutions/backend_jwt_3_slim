<?php
namespace App\Controllers;

use App\Models\SGPermiso;
use App\Models\SGPermisoVehiculo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Dompdf\Dompdf;
class PdfPreoperacionalController{

    private  $dompdf;

    private $permiso;

    private $customResponse;

    private $permisoVehiculo;

    private $html;

    public function __construct()
    {
        $this->dompdf = new Dompdf();

        $this->permiso = new SGPermiso();

        $this->customResponse = new CustomResponse();

        $this->permisoVehiculo = new SGPermisoVehiculo();
    }

    public function preoperacional(Request $request , Response $response  , $id)
    {
        try{
        $options = $this->dompdf->getOptions();
        $options->set(array('isRemoteEnabled' => true));
        $this->dompdf->setOptions($options);
        $this->first();
        //generando cabeceras;
        $this->cabeceras($id );
        $this->datos_vehiculo($id);
        $this->inspeccion($id);
        $this->last();
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper("letter");
        $this->dompdf->render();
        $name = "Preoperacional_".$id["id"];
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

    private function last()
    {
      $this->html .= '</body></html>';
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
        $html = '
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
              <h3 style:"font-family:font-family: sans-serif;">INSPECCIÓN PREOPERACIONAL DE VEHICULO Y/O MOTOCICLETA</h3>
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
        
      </tbody>
    </table>
    
    
      </div>
        
      ';
      $html .= $this->subtitulo("DATOS DEL VEHICULO Y/O MOTOCICLETA");
      $this->html .= $html;

         
    }


   private function subtitulo($name)
    {
       
      return '<div style="text-align:center;padding:0 20px;">
      <div style="background:#58C3D6><b style="font-size:10px">'.$name.'</b></div>
      </div>';
    }

    //buscar datos en 
    /**SELECT  hsv.vehiculo_nombre_tarjeta, hm.marca_nombre , hsv.vehiculo_color , hsv.vehiculo_placa , hsv.vehiculo_cilindraje , hsv.vehiculo_modelo , hsv.vehiculo_tipo  from han_sg_permisos_vehiculos hspv 
inner join han_sg_vehiculos hsv on hsv.vehiculo_id = hspv.vehiculo_id 
inner join han_marca hm on hsv.id_marca = hm.id_marca 
WHERE hspv.permiso_id = 2 */
    private function datos_vehiculo($id)
    {
      $vehiculo = $this->permisoVehiculo->selectRaw("han_sg_vehiculos.vehiculo_nombre_tarjeta, han_marca.marca_nombre , han_sg_vehiculos.vehiculo_color , han_sg_vehiculos.vehiculo_placa , han_sg_vehiculos.vehiculo_cilindraje , han_sg_vehiculos.vehiculo_modelo , han_sg_vehiculos.vehiculo_tipo ")
                                              ->join("han_sg_vehiculos" , "han_sg_vehiculos.vehiculo_id" , "=" , "han_sg_permisos_vehiculos.vehiculo_id")
                                              ->join("han_marca" , "han_marca.id_marca" , "=" , "han_sg_vehiculos.id_marca")
                                              ->where(["han_sg_permisos_vehiculos.permiso_id" => $id])
                                              ->get();
           $html = '<div style="padding:0 20px;">';
          if($vehiculo->count() > 0){
              foreach($vehiculo as $item)
              {
                $html .= '<table style="width: 100%" cellspacing="0" cellpadding="0" border="1"> 
                  <tr><td><b>TARJETA DE PROPIEDAD:</b></td><td>'.$item->vehiculo_nombre_tarjeta.'</td><td><b>MARCA:</b></td><td>'.$item->marca_nombre.'</td></tr>
                  <tr><td><b>PLACA:</b></td><td>'.$item->vehiculo_placa.'</td><td><b>COLOR:</b></td><td>'.$item->vehiculo_color.'</td></tr>
                  <tr><td><b>CILINDRAJE:</b></td><td>'.$item->vehiculo_cilindraje.'</td><td><b>MODELO:</b></td><td>'.$item->vehiculo_modelo.'</td></tr>
                </table>';
              }

              $html .='</div>';
          }else{
            $html .= '<div style="border: 1px solid; ">Sin registros</div></div>';
          }

          $this->html .= $html;
        
    }

    /**SELECT hsg.nombre ,hsg.tipo, hsvg.inspeccion,hsg.item  FROM han_sg_permisos_vehiculos hspv 
INNER JOIN han_sg_vehiculos_generalidades hsvg ON hsvg.permiso_vehiculo_id = hspv.permiso_vehiculo_id 
INNER JOIN han_sg_generalidades hsg on hsg.id_generalidades = hsvg.generalidades_id 
WHERE hspv.permiso_id = 2 order by hsg.item desc */
public function inspeccion($id){
  $html = $this->subtitulo("INSPECCIÓN");
  $generalidades = $this->permisoVehiculo->selectRaw("han_sg_generalidades.nombre ,han_sg_generalidades.tipo, han_sg_vehiculos_generalidades.inspeccion,han_sg_generalidades.item")
                ->join("han_sg_vehiculos_generalidades" , "han_sg_vehiculos_generalidades.permiso_vehiculo_id" , "=" ,"han_sg_permisos_vehiculos.permiso_vehiculo_id")
                ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_vehiculos_generalidades.generalidades_id")
                ->where(["han_sg_permisos_vehiculos.permiso_id" => $id])
                ->orderBy("han_sg_generalidades.item" , "desc")
                ->get();
  $html .= '<div style="padding:0 20px;">';
   if($generalidades->count() > 0)
   {
    $html .= '<table style="width: 100%" cellspacing="0" cellpadding="0" border="1">
  <tr><td><b>ELEMENTOS INSPECCIONADOS</b></td><td><b>ESTADO</b></td><td><b>ITEM</b></td></tr>
  ';
      foreach($generalidades as $item)
      {
        $html .= '
        
          <tr>
            <td>'.$item->nombre.'</td>
            <td>'.$item->inspeccion.'</td>
            <td>'.$item->item.'</td>
          </tr>
        
        ';  
      }


      $html .= '</table></div>';
   }else{
    $html .= '<div style="border: 1px solid; ">Sin registros</div></div>';
   }

  $this->html .= $html;
}


}


?>