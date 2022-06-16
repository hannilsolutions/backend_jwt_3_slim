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
    $app->get("/gps/list/{barrio}" , "ContratoGPSController:getContratoGps");
    $app->get("/municipios/{id}" , "ContratoGPSController:findMunicipios");
    $app->get("/barrios/{id}" , "ContratoGPSController:findBarrios");
});

//facturas
$app->group("/facturas" , function() use ($app){
    $app->post("/findByOne" , "FacturaController:findByOne");
});

//uploads
$app->group("/uploads" , function() use($app){
    $app->put("/:origin/:file" , "UploadsController:uploads");
});

//usuarios
$app->group("/usuarios" , function() use($app){
    $app->get("/list/{id}" , "UsuarioController:List");
    $app->get("/findByName/{name}" , "UsuarioController:findByName");
    $app->delete("/delete/{id}" , "UsuarioController:deleteById");
    $app->patch("/edit/{id}" , "UsuarioController:updateById");
});

//roles

$app->group("/roles" , function() use ($app){
    $app->get("/list" , "RolesController:findByRole");
    $app->get("/sidebar/{role}" , "RolesController:findSidebarByRol");
});
 