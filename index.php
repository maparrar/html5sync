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
        <script src="html5sync/client/jquery/jquery-2.1.0.min.js"></script>
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
                
                
                var stores=[
                    {
                        name:"Agricultor",
                        key:{keyPath:"id"},
                        indexes:[
                            {
                                name:"id",
                                key:"id",
                                params:{unique:true}
                            },
                            {
                                name:"nombre",
                                key:"nombre",
                                params:{unique:false}
                            },
                            {
                                name:"apellido",
                                key:"apellido",
                                params:{unique:false}
                            }
                        ]
                    }
                ]
                
                var parameters={
                    database: "agroplan",
                    version: 2,                //Versión de la base de datos
                    stores:stores   
                }
                var database=new Database(parameters,function(err){
                    var agricultores=[
                        {
                            "id":1,
                            "nombre":"pepito 1",
                            "apellido":"perez 1",
                            "cedula":"11111",
                            "direccion":"cra 1",
                            "telefono":"11111",
                            "municipio":1
                        },
                        {
                            "id":2,
                            "nombre":"pepito 2",
                            "apellido":"perez 2",
                            "cedula":"22222",
                            "direccion":"cra 2",
                            "telefono":"22222",
                            "municipio":1
                        }
                    ];

//                    database.add("Agricultor",agricultores,function(err){
//                        if(err){console.debug(err);}
//                    });
                    
                    
                    
                    
                    database.get("Agricultor",2,function(err,obj){
                        if(!err){
                            console.debug(obj);
                        }
                    });
                });
                
                
                
                
                
            });
        </script>
    </head>
    <body>
        <input type="button" id="reloadData" value="Recargar datos"/>
    </body>
</html>
