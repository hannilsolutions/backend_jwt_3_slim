<?php
return function($container)
{
    $container["GuestEntryController"] = function()
    {
        return new \App\Controllers\GuestEntryController;
    };

    $container["AuthController"] = function()
    {
      return new \App\Controllers\AuthController;
    };

    $container["PagoController"] = function()
    {
      return new \App\Controllers\PagoController;
    };
    
    $container["ClienteController"] = function()
    {
      return new \App\Controllers\ClienteController;
    };

    #agregar 

    $container["PagoMerkasController"] = function()
    {
      return new \App\Controllers\PagoMerkasController;
    };

    $container["SorteoController"] = function()
    {
      return new \App\Controllers\SorteoController;
    };

    $container["ContratoController"] = function()
    {
      return new \App\Controllers\ContratoController;
    };

    $container["FacturaController"] = function()
    {
      return new \App\Controllers\FacturaController;
    };

    $container["UploadsController"] = function()
    {
      return new \App\Controllers\UploadsController;
    };

    $container["UsuarioController"] = function()
    {
      return new \App\Controllers\UsuarioController;
    };

    $container["RolesController"] = function()
    {
      return new \App\Controllers\RolesController;
    };

    $container["ContratoGPSController"] = function()
    {
      return new \App\Controllers\ContratoGPSController;
    };

};