<!--
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
 -->
 
<?php
session_start();
$_SESSION['html5sync_userId']=6;
$_SESSION['html5sync_role']="role1";
?>
 
<!doctype html>
<html manifest="cache.manifest" type="text/cache-manifest">
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="html5sync/client/css/base.css">
        <link rel="stylesheet" type="text/css" href="html5sync/client/css/jquery-ui-1.10.4.css">
        <script src="html5sync/client/jquery/jquery-2.1.0.min.js"></script>
        <script src="html5sync/client/jquery/jquery-ui-1.10.4.js"></script>
        <script src="html5sync/client/core/Configurator.js"></script>
        <script src="html5sync/client/core/Connector.js"></script>
        <script src="html5sync/client/core/Html5Sync.js"></script>
        <script src="html5sync/client/core/Database.js"></script>
        <script type="text/javascript">
            $( document ).ready(function(){
                var html5Sync=new Html5Sync({
                    debugging:true,
                    showState:true
                },function(err){
                    console.debug(err);
                });
//                $("#reloadData").click(function(){
//                    html5Sync.forceReload(function(err){
//                        if(err){
//                            console.debug(err);
//                        }
//                    });
//                });
                
             $( "#tabs" ).tabs(); //PestaÃ±as jqueryui
                
                //Lista de parÃ¡metros que define la configuraciÃ³n de la base de datos
                var params={
                    database: "agroplan",  //Nombre de la base de datos
                    version: 1,                //VersiÃ³n de la base de datos
                    stores: [
                        {
                            name:"agricultor",
                            key:{keyPath:"cedula"},
                            indexes:[           //Lista de Ã­ndices del almacÃ©n, ver parÃ¡metros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
                                {
                                    name:"cedula",
                                    key:"cedula",
                                    params:{unique: true}
                                },
                                {
                                    name:"nombre", 
                                    key:"nombre", 
                                    params:{unique: false}
                                },
                                {
                                    name:"apellido",
                                    key:"apellido",
                                    params:{unique: false}
                                }
                            ]
                        },
                        {
                            name:"finca",
                            key:{keyPath:"id",keyAutoincrement:true},
                            indexes:[           //Lista de Ã­ndices del almacÃ©n, ver parÃ¡metros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
                                {
                                    name:"id", 
                                    key:"id", 
                                    params:{unique: true}
                                },                                {
                                    name:"agricultorid", 
                                    key:"agricultorid", 
                                    params:{unique: false}
                                },
                                {
                                    name:"nombre",
                                    key:"nombre",
                                    params:{unique: false}
                                }
                            ]
                        },
                        {
                            name:"lote",
                            key:{keyPath:"id"},
                            indexes:[           //Lista de Ã­ndices del almacÃ©n, ver parÃ¡metros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
                                {
                                    name:"id",
                                    key:"id",
                                    params:{unique: true}
                                },
                                {
                                    name:"fincaid", 
                                    key:"fincaid", 
                                    params:{unique: false}
                                },
                                {
                                    name:"nombre",
                                    key:"nombre",
                                    params:{unique: false}
                                }
                            ]
                        }
                    ]      
                };                
                var database=new Database(params,function(err){
                    if(err){
                        console.debug(err);
                    }else{
                        //Todo se debe hacer dentro del contexto de la creaciÃ³n de la base de datos
                        $("#addAgr").click(function(){
                            var nuevoAgr={
                                cedula:$("#agrCedula").val(),
                                nombre:$("#agrNombre").val(),
                                apellido:$("#agrApellido").val()
                            };
                            console.debug(nuevoAgr);
                            database.add("agricultor",nuevoAgr,function(err){
                                if(err){console.debug(err);}else{
                                    $("#agrCedulaConsulta").append("<option value="+$("#agrCedula").val()+">"+$("#agrNombre").val()+" "+$("#agrApellido").val()+"</option>");
                                    $("#tabs-1 input").val("");
                                }
                            });
                            
                        });
                        $("#addFinca").click(function(){
                            var nuevaFinc={
                                agricultorid:$("#agrCedulaConsulta").val(),
                                nombre:$("#finNombre").val(),
                                area:$("#finArea").val()
                            };
                            console.debug(nuevaFinc);
                            
                            database.add("finca",nuevaFinc,function(err){
                                if(err){console.debug(err);}
                            });
                            
                        });

                        database.list("agricultor",function(err,data){
                            if(err){console.debug(err);}else{
                                if(data.length>0){
                                    var i;
                                    for(i=0;i<data.length;i++){
                                        $("#agrCedulaConsulta").append("<option value="+data[i]['cedula']+">"+data[i]['nombre']+" "+data[i]['apellido']+"</option>");
                                    }
                                }
                            }        
                        });
                    }
                });
            });
        </script>
    </head>
    <body>
        <div id="tabs">
	<ul>
		<li><a href="#tabs-1">Agricultor</a></li>
		<li id="argAgricultorTab"><a href="#tabs-2">Finca</a></li>
		<li><a href="#tabs-3">Lote</a></li>
	</ul>
            <div id="tabs-1">
                <input type="text" id="agrCedula" placeholder="Cedula"/>
                <input type="text" id="agrNombre" placeholder="Nombre"/>
                <input type="text" id="agrApellido" placeholder="Apellido"/>
                <button id="addAgr">Agregar</button>
            </div>
            <div id="tabs-2">
                <select name="cedula-agricultor" id="agrCedulaConsulta">
                    <option value="0">Seleccione un agricultor</option>
                </select>
                <input type="text" id="finNombre" placeholder="Nombre de la finca"/>
                <input type="text" id="finArea" placeholder="Area"/>
                <button id="addFinca">Agregar</button>
            </div>
            <div id="tabs-3">                
            </div>
    </body>
</html>
