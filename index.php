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
                    
                    
                    
                    
                    
                    //Pruebas de database
//                    html5Sync.configurator.db.get("Parameters",false,0,function(err,data){
//                        if(err){
//                            console.debug(err);
//                        }else{
//                            console.debug(data);
//                        }
//                    });
                    
                    
                };
                /**
                 * Function to execute each sync
                 */
                function functionToExecEachSync(){
                    showStore($("#storeSelect").val());
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
                            //Asigna los eventos y crea el formulario para agregar objetos
                            var titles=table.find("#html5sync_store_titles");
                            var html5sync_new=$("#html5sync_new");
                            html5sync_new.attr("data-store",$("#storeSelect").val());
                            var html="<h3>New object</h3>";
                            titles.find(".html5sync_title").each(function(){
                                var divField=$(this);
                                if(divField.hasClass("html5sync_pk")){
                                    html5sync_new.attr("data-pk",divField.attr("data-field"));
                                }else{
                                    html+='<div class="html5sync_field">';
                                    html+='<label for="'+divField.attr("data-field")+'">'+divField.attr("data-field")+'</label><br/>';
                                    html+='<input type="text" id="'+divField.attr("data-field")+'" value="" />';
                                    html+='</div>';
                                }
                            });
                            html+='<button id="html5sync_add">Add</button>';
                            html5sync_new.empty().append(html);
                            //Si se hace click en el botón de insertar
                            $("#html5sync_add").click(function(){
                                var store=html5sync_new.attr("data-store");
                                var row={};
                                html5sync_new.find(".html5sync_field").each(function(){
                                    var divInput=$(this).find("input");
                                    row[divInput.attr("id")]=divInput.val();
                                });
                                html5Sync.database.add(store,row,function(err,index){
                                    if(err){
                                        debug(err);
                                    }else{
                                        debug("Row "+index+" was insert on "+storeName);
                                        showStore(storeName);
                                    }
                                });
                            });
                            
                            
                            //Asigna los eventos para las filas
                            $(".html5sync_edit_row").click(function(){
                                var row=$(this).parent().parent();
                                var html5sync_edit=$("#html5sync_edit");
                                html5sync_edit.attr("data-store",$("#storeSelect").val());
                                var html="<h3>Update object</h3>";
                                row.find("td").each(function(){
                                    var divField=$(this);
                                    if(divField.attr("data-field")!==undefined){
                                        html+='<div class="html5sync_field">';
                                        html+='<label for="'+divField.attr("data-field")+'">'+divField.attr("data-field")+'</label><br/>';
                                        if(divField.hasClass("html5sync_pk")){
                                            html+='<input type="text" id="'+divField.attr("data-field")+'" value="'+divField.text()+'" disabled/>';
                                            html5sync_edit.attr("data-pk",divField.attr("data-field"));
                                            html5sync_edit.attr("data-key",divField.text());
                                        }else{
                                            html+='<input type="text" id="'+divField.attr("data-field")+'" value="'+divField.text()+'" />';
                                        }
                                        html+='</div>';
                                    }
                                });
                                html+='<button id="html5sync_update">Update</button>';
                                html5sync_edit.empty().append(html);
                                //Si se hace cliek en el botón de actualizar
                                $("#html5sync_update").click(function(){
                                    var store=html5sync_edit.attr("data-store");
                                    var key=html5sync_edit.attr("data-key");
                                    var row={};
                                    html5sync_edit.find(".html5sync_field").each(function(){
                                        var divInput=$(this).find("input");
                                        row[divInput.attr("id")]=divInput.val();
                                    });
                                    html5Sync.database.update(store,false,key,row,function(err,newRow){
                                        if(err){
                                            debug(err);
                                        }else{
                                            debug("Row "+key+" was updated in "+storeName);
                                            showStore(storeName);
                                            html5sync_edit.empty();
                                        }
                                    });
                                });
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
                                stringRow+='<td data-field="'+$(this).attr("data-field")+'" class="html5sync_pk">'+row[$(this).attr("data-field")]+'</td>';
                            }else{
                                stringRow+='<td data-field="'+$(this).attr("data-field")+'">'+row[$(this).attr("data-field")]+'</td>';
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
            <div id="html5sync_store">
                <div id="html5sync_caption">Select a store: <select id="storeSelect"></select></div>
                <div id="html5sync_content">
                    <table id="html5sync_table"></table>
                </div>
            </div>
            <div id="html5sync_new"></div>
            <div id="html5sync_edit"></div>
        </div>
    </body>
</html>
