<?php

namespace App\Controllers;

use App\Models\Han_Uploads;
use App\Models\Pago;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Psr\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;


class UploadsController
{

    protected  $customResponse;

    protected $uploads;

    protected  $validator;

    protected $documento;

    protected $pago;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->uploads = new Han_Uploads();

         $this->validator = new Validator();

         $this->pago = new Pago();
    }

    /*
    *POST file */
    

    public function uploads(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "categoria"=>v::notEmpty(),
           "fecha" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['file_uploads'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

           $filename = $this->moveUploadedFile(CustomRequestHandler::getParam($request,"categoria") , $uploadedFile , CustomRequestHandler::getParam($request,"fecha"));
            //$destino = CustomRequestHandler::getParam($request , "categoria");

            //$directory = __DIR__."/Files/$destino";

           // $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

            //$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        
           // $filename = sprintf('%s.%0.8s', $basename, $extension);

           // $uploadedFile->moveTo("/home/internet/public_html/apps/Files/$destino/$filename");

            //$response->write('uploaded ' . $filename . '<br/>');
            

            $responseMessage = $filename;
        }

        //$responseMessage = "cargado con exito";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    function moveUploadedFile( $destino , $uploadedFile , $fecha)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php

        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $path      = "/home/internet/public_html/apps/Files/".$destino;

        if (!is_dir($path)) {

            mkdir($path, 0777, true);
        }

        $uploadedFile->moveTo("/home/internet/public_html/apps/Files/$destino/$filename");

        //$uploadedFile->moveTo("/var/www/html/Files/$destino/$filename");

        $this->uploads->create([
                    "titulo" => $filename,
                    "categoria" => $destino,
                    "tipo"      => $extension,
                    "fecha"     => $fecha
                ]);

        return $filename;
    }

    /**
     * ENDPOINT GET list by category with limit variable
     * */

    public function listCategoria(Request $request , Response $response , $data )
    {
        $getList = $this->uploads->where("categoria" ,  "=" , $data["categoria"])->orderBy("id" , "desc")->limit($data["count"])->get();

        $this->customResponse->is200Response($response , $getList);
    }

    /**
     * ENDPOINT DELETE eliminar upload and archivo
     * */
    public function delete(Request $request , Response $response , $id)
    {
        $this->validator->validate($request , [
            "name" => v::notEmpty(),
            "category" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {

            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $folder = CustomRequestHandler::getParam($request  , "category");

        $filename = CustomRequestHandler::getParam($request , "name");

        $pathFile = "/home/internet/public_html/apps/Files/".$folder."/".$filename;

        if (file_exists($pathFile)) {
            #comienza a eliminar archivo
            if(!unlink($pathFile))
            {
                $responseMessage = "Error eliminando el archivo";

                return $this->customResponse->is400Response($response , $responseMessage);
            }

        }else {

            $responseMessage = "archivo no encontrado";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->uploads->where(["id" => $id])->delete();

        $responseMessage = "eliminado";

        $this->customResponse->is200Response($response , $responseMessage);

    }

    /**
     * ENDPOINT GET supergiros cargar archivo excel a base de datos
     * */
    public function supergirosCargue(Request $request , Response $response , $data)
    {
       if(!$this->recorrerArchivo($data))
       {
         $responseMessage = "error recorriendo archivo";

         return $this->customResponse->is400Response($response , $responseMessage);
       }

       $this->uploads->where("id" , "=" , $data["id"])->update([
            "estado" => 1,
       ]);

       $responseMessage = "cargado";

       $this->customResponse->is200Response($response , $responseMessage );
    }

    /**
     * functiom para recorrer el excel de supergiros
     */
    public function recorrerArchivo($data)
    {
         try{
            #iniciamos abriendo el excel
         $ruta = "/home/internet/public_html/apps/Files/Supergiros/".$data["filename"];
        
         $this->documento = IOFactory::load($ruta);

         $totalHojas = $this->documento->getSheetCount();

         $validacion = 1;

         $temp = array();

         $j = 1;

         for($i = 0 ; $i < $totalHojas; $i++)
         {
            $hojaActual = $this->documento->getSheet($i);

            #recorremos las  filas
            foreach($hojaActual->getRowIterator() as $fila)
            {
                #recorremos celda x celda de cada $fila
                foreach($fila->getCellIterator() as $celda)
                {
                    #asignamos el valor a la variable $valor
                    $valor = $celda->getValue();
                    
                    #si el valor es nulo, continuar con la siguiente fila
                    if($valor == NULL)
                    {
                        continue;
                    }
                    #guardamos la info en array temp
                    $temp[$j] = $valor;

                    $j++;
                    #fin del recorrido de las celdas
                }
                #salvar
                $save = $this->savePago($temp);

                $j = 1;
             
                #fin del recorrido de las filas
            }
            #fin del recorrio de las hojas
         }
            return true;

         }  catch(Exception $e)
         {
            return false;
         }
    }

    /**
     * function para transformar fecha d/m/Y en Y-m-d
     * */
    public function explodeFecha($date)
    {
        $fecha = explode("/", $date);

        $transform = $fecha[2]."-".$fecha[1]."-".$fecha[0];

        return $transform;
    }

    /**
     * function para almacenar en base de datos los datos del excel
     * */
    public function savePago($temp)
    {
        #transformamos la fecha en valores y-m-d
        $getTransformDate = $this->explodeFecha($temp[1]);

        #validar que no exista el pago
        $getTransaccion = $this->pago->where("id_transaccion" , "=" , $temp[3])->count();
        
        if($getTransaccion > 0)
        {
            return false;
        }

        #enviamos a guardar los datos

        $this->pago->create([
            "fecha_recaudo"=>   $getTransformDate,
            "hora_recaudo"=>    $temp[2],
            "id_transaccion"=>  $temp[3],
            "cc_asesor"=>       $temp[4],
            "nombre_asesor"=>   $temp[5],
            "codigo_pto_vta"=>  $temp[6],
            "nombre_pto_vta"=>  $temp[7],
            "nombre_convenio"=> $temp[8],
            "numero_referencia"=>$temp[9],
            "valor_total"=>     $temp[10],
            "cc_cliente"=>      $temp[11],
            "nombre_cliente"=>  $temp[12]
         ]);

         return true;
    }




   


}

?>