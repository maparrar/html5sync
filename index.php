<?php
session_start();
$_SESSION['html5sync_userId']=6;
$_SESSION['html5sync_role']="role1";
?>
<!--
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
 -->
 
<!doctype html>
<html manifest="cache.manifest" type="text/cache-manifest">
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="client/css/base.css">
        <link rel="stylesheet" type="text/css" href="exampleFiles/example.css">
        <script src="client/jquery/jquery-2.1.0.min.js"></script>
        <script src="client/core/Configurator.js"></script>
        <script src="client/core/Connector.js"></script>
        <script src="client/core/Html5Sync.js"></script>
        <script src="client/core/Database.js"></script>
        <script type="text/javascript">
            $( document ).ready(function(){
                var html5Sync=new Html5Sync({
                    debugging:true,
                    showState:true,
                    syncCallback:functionToExecEachSync
                },function(err){
                    if(err){
                        console.debug(err);
                    }else{
                        debug("html5sync, without errors","good");
                        functionToExecuteWhenDatabaseLoaded();
                    }
                });
                $("#reloadData").click(function(){
                    html5Sync.forceReload(function(err){
                        if(err){
                            console.debug(err);
                        }
                    });
                });
                
                
                
                /**
                 * Función que se ejectua cuando la base de datos se cargó correctamente
                 * Safely use of database... put your code here
                 * @returns {undefined}
                 */
                function functionToExecuteWhenDatabaseLoaded(){
                    var stores=html5Sync.database.getStoreNames();
                    
                    //Muestra el primer almacén
                    if(stores.length>0){
                        showStore(stores[0]);
                    }
                    
                    for(var i=0;i<stores.length;i++){
                        $("#storeSelect").append('<option value="'+stores[i]+'">'+stores[i]+'</option>');
                    }
                    
                    //Eventos
                    $("#storeSelect").change(function(){
                        showStore($(this).val());
                    });
                };
                /**
                 * Function to execute each sync
                 */
                function functionToExecEachSync(){
                    var store=$.trim($("#store").val());
                    var key=$.trim($("#key").val());
                    if(store!==""&&key!==""){
//                        html5Sync.database.get(store,key,function(err,objects){
//                            if(!err&&objects.length>0){
//                                for(var i in objects){
//                                    $("#objects").text(objToString(objects[i]));
//                                }
//                            }else{
//                                $("#objects").text("");
//                            }
//                        });
                    }
                };
                
                
                /**
                 * Muestra los datos del almacén
                 * @param {string} storeName Nombre del store
                 */
                function showStore(storeName){
                    var table=$("#html5sync_table");
                    var store=html5Sync.database.getStore(storeName);
                    var indexNames=store.indexNames;
                    var key=store.keyPath;
                    table.empty();
                    showTitles(key,indexNames);
                    html5Sync.database.list(storeName,function(err,list){
                        if(!err){
                            showObjects(list);
                            //Asigna los eventos para las filas
                            $(".html5sync_edit_row").click(function(){
                                var key=$(this).parent().parent().find(".html5sync_pk").text();
                                console.debug(key);
                            });
                            $(".html5sync_delete_row").click(function(){
                                var row=$(this).parent().parent();
                                var key=row.find(".html5sync_pk").text();
                                html5Sync.database.delete(storeName,key,function(err){
                                    if(err){
                                        debug(err);
                                    }else{
                                        debug("Row "+key+" was deleted from "+storeName);
                                        row.remove();
                                    }
                                });
                            });
                            $(".html5sync_edit_row").click(function(){
                                var row=$(this).parent().parent();
                                var key=row.find(".html5sync_pk").text();
                                html5Sync.database.delete(storeName,key,function(err){
                                    if(err){
                                        debug(err);
                                    }else{
                                        debug("Row "+key+" was deleted from "+storeName);
                                        row.remove();
                                    }
                                });
                            });
                        }
                    });
                };
                /**
                 * Muestra los índices
                 * @param {string} key Nombre de la PK
                 * @param {string[]} indexes Lista de índices del store
                 */
                function showTitles(key,indexes){
                    var table=$("#html5sync_table");
                    table.append('<tr id="html5sync_store_titles"></tr>');
                    var titles=table.find("#html5sync_store_titles");
                    for(var i=0;i<indexes.length;i++){
                        if(indexes[i]===key){
                            titles.prepend('<th class="html5sync_title html5sync_pk" data-field="'+indexes[i]+'">'+indexes[i]+'</th>');
                        }else{
                            titles.append('<th class="html5sync_title" data-field="'+indexes[i]+'">'+indexes[i]+'</th>');
                        }
                    }
                    titles.prepend('<th class="html5sync_options" data-field="options">options</th>');
                };
                /**
                 * Muestra el contenido de la tabla
                 * @param {object[]} objects Lista de datos del store
                 */
                function showObjects(objects){
                    var table=$("#html5sync_table");
                    var titles=table.find(".html5sync_title");
                    for(var i=0;i<objects.length;i++){
                        var row=objects[i];
                        var stringRow='<tr class="html5sync_row" >';
                        stringRow+='<td><div class="html5sync_edit_row">edit</div><div class="html5sync_delete_row">delete</div></td>';
                        titles.each(function(){
                            if($(this).hasClass("html5sync_pk")){
                                stringRow+='<td class="html5sync_pk">'+row[$(this).attr("data-field")]+'</td>';
                            }else{
                                stringRow+='<td>'+row[$(this).attr("data-field")]+'</td>';
                            }
                        });
                        stringRow+='</td>';
                        table.append(stringRow);
                    }
                };
            });
        </script>
    </head>
    <body>
        <input type="button" id="reloadData" value="Recargar datos"/>
        <div id="example">
            <div id="html5sync_edit">
                <input id="store" type="text" placeholder="store name"/>
                <input id="key" type="text" placeholder="key"/>
                <input id="search" type="button" value="search"><br>
            </div>
            <div id="html5sync_store">
                <div id="html5sync_caption">Select a store: <select id="storeSelect"></select></div>
                <div id="html5sync_content">
                    <table id="html5sync_table"></table>
                </div>
            </div>
        </div>
    </body>
</html>
