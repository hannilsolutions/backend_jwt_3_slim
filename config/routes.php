<?php

/*$app->post("/create-guest","GuestEntryController:createGuest");

$app->get("/view-guests" ,"GuestEntryController:viewGuests");

$app->get("/get-single-guest/{id}","GuestEntryController:getSingleGuest");

$app->patch("/edit-single-guest/{id}","GuestEntryController:editGuest");

$app->delete("/delete-guest/{id}","GuestEntryController:deleteGuest");

$app->get("/count-guests" ,"GuestEntryController:countGuests");*/

$app->group("/pagos", function() use ($app){
    $app->post("/save" , "PagoController:save");
    $app->get("/all" , "PagoController:all");
    $app->patch("/reversado/{id}" , "PagoController:reversarPago");
    $app->get("/{id}" , "PagoController:findOne");
    $app->get("/suma/mes" , "PagoController:sumaMes");
    $app->post("/findByBetween" , "PagoController:findByBetween");
});

$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:Login");
    $app->post("/register","AuthController:Register");
    $app->get("/validate/{jwt}" , "AuthController:Validate");
});

$app->group("/clientes" , function() use($app){
    $app->post("/deuda" , "ClienteController:deudas");
});

$app->group("/merkas" , function() use($app){
    $app->post("/save" , "PagoMerkasController:save");
    $app->post("/count" , "PagoMerkasController:countPagos");
    $app->post("/all" , "PagoMerkasController:all");
    $app->get("/count/estado" , "PagoMerkasController:countEstado");
    $app->patch("/edit/{id}" , "PagoMerkasController:updateReciboCaja");
    $app->post("/pagoscontrol" , "PagoMerkasController:findByBetween");
});

$app->group("/sorteo" , function() use ($app){
    $app->get("/ramdon/{id}" , "SorteoController:findByMunicipio"); 
    $app->patch("/edit/{id}" , "SorteoController:updateById");
    $app->get("/ganadores/all" , "SorteoController:getGanadores");
});

//buscar por contratos

$app->group("/contratos" , function() use ($app){
    $app->get("/findByCus/{id}" , "ContratoController:findByCus");
    $app->post("/saveEncuesta" , "ContratoController:preferenciaFactura");
    $app->post("/gps/save" , "ContratoGPSController:save");
    $app->post("/gps/list" , "ContratoGPSController:getContratoGps");
    $app->get("/municipios/{id}" , "ContratoGPSController:findMunicipios");
    $app->get("/barrios/{id}" , "ContratoGPSController:findBarrios");
    #gps betwenn
    $app->post("/gps/findByBetween" , "ContratoGPSController:findByBetween");
});

//facturas
$app->group("/facturas" , function() use ($app){
    $app->post("/findByOne" , "FacturaController:findByOne");
});

//uploads
$app->group("/uploads" , function() use($app){
    $app->post("/origin" , "UploadsController:uploads");
});

//usuarios
$app->group("/usuarios" , function() use($app){
    $app->get("/list/{id}" , "UsuarioController:List");
    $app->get("/findByName/{name}" , "UsuarioController:findByName");
    $app->delete("/delete/{id}" , "UsuarioController:deleteById");
    $app->patch("/edit/{id}" , "UsuarioController:updateById");
    $app->get("/findByIdempresa/{id}" , "UsuarioController:findByIdempresa");
});

//roles

$app->group("/roles" , function() use ($app){
    $app->get("/list" , "RolesController:findByRole");
    $app->get("/sidebar/{role}","RolesController:findSidebarByRol");
    $app->get("/rol/{role}" , "RolesController:findRoleByRol");
    #cargar relations
    $app->post("/relations/save" , "RolesController:saveRelations");
    #grupo menu
    $app->get("/group/list" ,   "RolesController:findByGroup");
    $app->post("/group/save" , "RolesController:saveGroup");
    #Vistas
    $app->get("/views/list" ,   "RolesController:findByViews");
    $app->post("/views/save" ,  "RolesController:saveView");
    
});

//sistema de gestion sst
$app->group("/seguridad" , function() use ($app){
    #clasificaciones
    $app->post("/clasificacion/save" , "SGClasificacionController:save");
    $app->get("/clasificacion/list" , "SGClasificacionController:list");
    #peligros
    $app->post("/peligro/save" , "SGPeligroController:save");
    $app->post("/peligro/list" , "SGPeligroController:list");
    $app->delete("/peligro/deleteById/{id}" , "SGPeligroController:deleteById");
    #controles
    $app->post("/controles/save" , "SGControlesController:save");
    $app->get("/controles/findByPeligro/{id}" , "SGControlesController:findByPeligro");
    $app->delete("/controles/deleteById/{id}" , "SGControlesController:deleteById");

    #generalidades
    #reporta disct de empresa
    $app->get("/generalidades/disct/{id}" , "SGGeneralidadesController:tipoDisct");
    $app->post("/generalidades/findByTipo" , "SGGeneralidadesController:findByTipo");

    #permiso de trabajo
    $app->post("/permiso/save" , "SGPermisoController:save");
    $app->get("/permiso/findByUsuarioOpen/{id}" , "SGPermisoController:findByUsuarioOpen");

    #tiposPermisos
    $app->get("/tipoTrabajo/{id}" , "SGTipoTrabajoController:getTipoTrabajo");

    #empleados_permisos
    $app->post("/permisosEmpleados/save" , "SGPermisosEmpleadosController:save");
    $app->get("/permisosEmpleados/list/{id}" , "SGPermisosEmpleadosController:findByIdpermiso");
    $app->delete("/permisosEmpleados/deleteById/{id}" , "SGPermisosEmpleadosController:deleteById");
    $app->get("/permisosEmpleados/findByEmpleado/{id}" , "SGPermisosEmpleadosController:findByEmpleado");

});
 