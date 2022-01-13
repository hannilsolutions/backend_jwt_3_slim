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