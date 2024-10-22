<?php

/*$app->post("/create-guest","GuestEntryController:createGuest");

$app->get("/view-guests" ,"GuestEntryController:viewGuests");

$app->get("/get-single-guest/{id}","GuestEntryController:getSingleGuest");

$app->patch("/edit-single-guest/{id}","GuestEntryController:editGuest");

$app->delete("/delete-guest/{id}","GuestEntryController:deleteGuest");

$app->get("/count-guests" ,"GuestEntryController:countGuests");*/



$app->group("/auth",function() use ($app){

    $app->post("/login","AuthController:Login");
    $app->post("/register","AuthController:Register");
    $app->get("/validate/{jwt}" , "AuthController:Validate");
    $app->post("/recovery" , "AuthController:recovery");
    $app->post("/recoveryT/tokevalidate" , "AuthController:tokenValidateRecovery");
    $app->post("/recoveryP/newPassword" , "AuthController:newPassword");
});






//uploads
$app->group("/uploads" , function() use($app){
    $app->post("/origin" , "UploadsController:uploads");
    $app->get("/listCategoria/{count}/{categoria}" , "UploadsController:listCategoria");
    $app->delete("/delete/{id}" , "UploadsController:delete");
    $app->get("/supergiros/cargue/{id}/{filename}" , "UploadsController:supergirosCargue");
});

//usuarios
$app->group("/usuarios" , function() use($app){
    $app->get("/list/{id}" , "UsuarioController:List");
    $app->get("/findByName/{name}" , "UsuarioController:findByName");
    $app->delete("/delete/{id}" , "UsuarioController:deleteById");
    $app->patch("/edit/{id}" , "UsuarioController:updateById");
    $app->get("/findByIdempresa/{id}" , "UsuarioController:findByIdempresa");
    $app->get("/firmaElectronica/generate/{id}" , "UsuarioController:generateFirmaElectronica");
    $app->get("/findKeyById/{id}" , "UsuarioController:findKeyById");
    $app->patch("/updatedPassword/{id}" , "UsuarioController:updatedPassword");
    $app->post("/datospersonales/save" , "DatosPersonalesController:save");
    $app->get("/datospersonales/findbyid/{id}" , "DatosPersonalesController:findById");
    $app->patch("/datospersonales/updated/{id}" , "DatosPersonalesController:updated");
    $app->post("/findnameempresa" , "UsuarioController:findByNameAndEmpresa");
    $app->patch("/updatedUser/{id}", "UsuarioController:updatedUserMailAndPass");
    #buscar usuarios por nombre y empresa
    $app->post("/find/name/empresa" , "UsuarioController:findNameAndEmpresa");
    $app->post("/imagen/update" , "UsuarioController:changeImagen");
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

    #procedimientos
    $app->get("/procedimiento/list" , "SGProcedimientoController:generalidades_procedimientos");
    $app->post("/procedimiento" , "SGProcedimientoController:save");
    $app->get("/procedimiento" , "SGProcedimientoController:findByUserAndPermiso");

    #preguntas
    $app->get("/preguntas/findbypermisoempleado", "SGPermisoAptitudController:find_by_permiso_and_empleado");
    $app->post("/preguntas/updateaptitud" , "SGPermisoAptitudController:update_aptitud");
    
    #Reportes
    $app->post("/reportes/treeNode" , "SGReportesController:treeNode");

    #observaciones
    $app->post("/observacion/save" , "SGObservacionController:save");
    $app->get("/observacion/{id}" , "SGObservacionController:findByIdPermiso");
    $app->delete("/observacion/{id}" , "SGObservacionController:delete");
    $app->post("/observacion/img" , "SGObservacionController:imagen");


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
    $app->post("/generalidades/save" , "SGGeneralidadesController:save");

    #permiso de trabajo
    $app->post("/permiso/save" , "SGPermisoController:save");
    $app->get("/permiso/findByUsuarioOpen/{id}" , "SGPermisoController:findByUsuarioOpen");
    $app->get("/permiso/findById/{id}" , "SGPermisoController:findById");
    $app->get("/permiso/findByIdEmpresa/{id}", "SGPermisoController:findByIdEmpresa");
    $app->post("/permiso/findByIdUsuarioActive" , "SGPermisoController:findByIdUsuarioActive");
    $app->delete("/permiso/inactive/{id}" , "SGPermisoController:inactive");
    $app->delete("/permiso/cerrado/{id}" , "SGPermisoController:cerrado");
    #informacion de permiso completa para firmar
    $app->get("/permiso/final/{id}" , "SGPermisosEmpleadosController:permisoFinal");


    #peligros_empleado
    $app->post("/permisosPeligros/save" , "SGPermisosPeligrosController:save");
    $app->delete("/permisosPeligros/delete/{id}" , "SGPermisosPeligrosController:delete");
    $app->get("/permisosPeligros/listByPermiso/{id}" , "SGPermisosPeligrosController:listByPermiso");

    #tiposPermisos
    $app->get("/tipoTrabajo/{id}" , "SGTipoTrabajoController:getTipoTrabajo");

    #empleados_permisos
    $app->post("/permisosEmpleados/save" , "SGPermisosEmpleadosController:save");
    $app->get("/permisosEmpleados/list/{id}" , "SGPermisosEmpleadosController:findByIdpermiso");
    $app->delete("/permisosEmpleados/deleteById/{id}" , "SGPermisosEmpleadosController:deleteById");
    $app->get("/permisosEmpleados/findByEmpleado/{id}" , "SGPermisosEmpleadosController:findByEmpleado");
    $app->post("/permisosEmpleados/firmarEmpleado" , "SGPermisosEmpleadosController:firmarEmpleado");
    $app->post("/permisosJefes/firmarPermiso" , "SGPermisosEmpleadosController:firmarJefe");
    $app->get("/permisosEmpleados/active/{id}" , "SGPermisosEmpleadosController:listEmpleado");
    $app->post("/permisosEmpleados/info" , "SGPermisosEmpleadosController:inforParaFirmaEmpleado");
    $app->post("/permisosEmpleados/infoAdd" , "SGPermisosEmpleadosController:infoParaFirmarCreadorEmpleado");
  

    $app->post("/permisosEmpleados/findByIdPermisoAndIdUser" , "SGPermisosEmpleadosController:firmaFindByIdPermisoAndIdUser");
    #empleado_generalidades
    $app->post("/empleadoGeneralidades/create" , "SGEmpleadoGeneralidadesController:create");
    $app->post("/empleadoGeneralidades/findByEmpleadoAndPermisoAndTipo" , "SGEmpleadoGeneralidadesController:findByEmpleadoAndPermisoAndTipo");
    $app->post("/empleadoGeneralidades/findByEmpleadoPermisoTrabajoIsNotNull" , "SGEmpleadoGeneralidadesController:findByEmpleadoPermisoTrabajoIsNotNull");
    $app->patch("/empleadoGeneralidades/editActive/{id}" , "SGEmpleadoGeneralidadesController:editActive");

    #empresa
    $app->get("/empresa/list" , "SGEmpresaController:list");
    $app->patch("/empresa/updated/{id}" , "SGEmpresaController:updated");

    #emailsend
    $app->post("/sendMail/tokenFirma" , "SGEmailController:sendMailFirma");
    $app->post("/tokenFirma/validate" , "SGEmailController:validateToken");

    #vehiculos
    $app->post("/vehiculo/save" , "SGVehiculoController:save");
    $app->get("/vehiculo/listFindByIdUsuario/{id}" , "SGVehiculoController:listFindByIdUsuario");
    $app->get("/vehiculo/findById/{id}" , "SGVehiculoController:findById");
    $app->get("/vehiculo/listFindByEmpresa/{id}" , "SGVehiculoController:listFindByEmpresa");
    $app->delete("/vehiculo/delete/{id}" , "SGVehiculoController:deleteById");
    $app->patch("/vehiculo/updated/{id}" , "SGVehiculoController:updated");
    $app->post("/vehiculo/findByEmpresaAndPlaca" , "SGVehiculoController:findByEmpresaAndPlaca");

    #preoperacion vehiculos
    $app->post("/permisosVehiculos/save" , "SGPermisosVehiculoController:save");
    $app->get("/permisosVehiculos/findByPermiso/{id}" , "SGPermisosVehiculoController:findByPermiso");
    $app->delete("/permisosVehiculos/delete/{id}" , "SGPermisosVehiculoController:delete");
    $app->patch("/permisosVehiculos/updated/{id}" , "SGPermisosVehiculoController:updated");
    
    #generalidesvehiculo
    $app->get("/vehiculoGeneralidades/disctItem/{id}" , "SGVehiculoGeneralidadesController:disctGeneralidades");
    $app->post("/vehiculoGeneralidades/findByNameGeneralidadesVehiculos" , "SGVehiculoGeneralidadesController:findByNameGeneralidadesVehiculos");
    $app->patch("/vehiculoGeneralidades/editInspeccion/{id}" , "SGVehiculoGeneralidadesController:editInspeccion");


    #Documentos
    $app->post("/documento/save" , "SGDocumentoController:save");
    $app->post("/documento/findByTipo" , "SGDocumentoController:findByTipo");
    $app->get("/documento/{id}" , "SGDocumentoController:getDocumentoUrl");

    #firmas
    $app->post("/firmas/save" , "SGFirmaController:save");
    $app->get("/firmas/findByIdEmpresa/{id}" , "SGFirmaController:getFindByIdEmpresa");
    $app->get("/firmas/all" , "SGFirmaController:findAll");

    #detalleFirmas
    $app->post("/detalle/firmas" , "SGDetalleFirmasController:create");
    $app->get("/detalle/firmas/{id}" , "SGDetalleFirmasController:listByIdPermiso");
    $app->post("/detalle/firmar" , "SGDetalleFirmasController:firmarPermisoJefes");

});

#marcas
$app->group("/marcas" , function() use ($app) { 
    #save
    $app->post("/save" , "MarcasController:save");
    $app->get("/list" , "MarcasController:list");
    $app->delete("/delete/{id}" , "MarcasController:delete");
});



/***generar pdf */
$app->group("/pdf", function() use ($app){
    $app->get("/permiso/{id}" , "generarPdfController:permisoAlturas");
    $app->get("/preoperacional/{id}" , "PdfPreoperacionalController:preoperacional");
    $app->get("/permisosEmpleados/{id}" , "SGPermisosEmpleadosController:getListEmpleadosWithDatosPersonales");
} );



 /**notifications */
 $app->group("/notifications" , function() use($app){
    $app->get("/all","SGNotificationsController:list_by_five");
    $app->put("/changeEstado/{id}","SGNotificationsController:updated");
 });
 