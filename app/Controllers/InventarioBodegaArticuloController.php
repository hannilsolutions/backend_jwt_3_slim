
<?php

namespace App\Controllers;

use App\Models\InventarioBodegaArticulo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioBodegaArticuloController
{	
	protected  $customResponse;

    protected  $bodegaArt;

    protected  $validator;

       public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->bodegaArt = new InventarioBodegaArticulo();

         $this->validator = new Validator();
    }


   //updated

    


}

<?