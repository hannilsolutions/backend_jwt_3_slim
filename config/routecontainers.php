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

    $container["SGClasificacionController"] = function()
    {
      return new \App\Controllers\SGClasificacionController;
    };

    $container["SGPeligroController"] = function()
    {
      return new \App\Controllers\SGPeligroController;
    };

    $container["SGControlesController"] = function()
    {
      return new \App\Controllers\SGControlesController;
    };

    $container["SGGeneralidadesController"] = function()
    {
      return new \App\Controllers\SGGeneralidadesController;
    };

    $container["SGPermisoController"] = function()
    {
      return new \App\Controllers\SGPermisoController;
    };

    $container["SGTipoTrabajoController"] = function()
    {
      return new \App\Controllers\SGTipoTrabajoController;
    };

    $container["SGPermisosEmpleadosController"] = function()
    {
      return new \App\Controllers\SGPermisosEmpleadosController;
    };

    $container["SGEmpleadoGeneralidadesController"] = function()
    {
      return new \App\Controllers\SGEmpleadoGeneralidadesController;
    };

    $container["SGEmpresaController"] = function()
    {
      return new \App\Controllers\SGEmpresaController;
    };

    $container["SGEmailController"] = function()
    {
      return new \App\Controllers\SGEmailController;
    };

    $container["SGVehiculoController"]  = function()
    {
      return new \App\Controllers\SGVehiculoController;
    };

    $container["MarcasController"] = function()
    {

      return new \App\Controllers\MarcasController;
    };

    $container["SGPermisosPeligrosController"] = function()
    {
      return new \App\Controllers\SGPermisosPeligrosController;
    };

    $container["SGDocumentoController"] = function() {

      return new \App\Controllers\SGDocumentoController;
    };

    $container["SGPermisosVehiculoController"]= function() {
      return new \App\Controllers\SGPermisosVehiculoController;
    };

    $container["SGVehiculoGeneralidadesController"] = function(){
      return new \App\Controllers\SGVehiculoGeneralidadesController;
    };

    $container["InventarioArticuloController"]= function()
    {
      return new \App\Controllers\InventarioArticuloController;
    };

    $container["InventarioProveedorController"]= function()
    {
      return new \App\Controllers\InventarioProveedorController;
    };


    $container["InventarioBodegaController"]= function()
    {
      return new \App\Controllers\InventarioBodegaController;
    };


     $container["InventarioIngresoController"]= function()
    {
      return new \App\Controllers\InventarioIngresoController;
    };


     $container["InventarioIngresoDetalleController"]= function()
    {
      return new \App\Controllers\InventarioIngresoDetalleController;
    };



     $container["InventarioBodegaArticuloController"]= function()
    {
      return new \App\Controllers\InventarioBodegaArticuloController;
    };


    $container["InventarioTransferenciaBodegasController"]= function()
    {
      return new \App\Controllers\InventarioTransferenciaBodegasController;
    };



};