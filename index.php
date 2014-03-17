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
$_SESSION['html5sync_userId']=2;
$_SESSION['html5sync_role']="ventas";
?>
 
<!doctype html>
<html manifest="cache.manifest" type="text/cache-manifest">
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="html5sync/client/css/base.css">
        <script src="html5sync/client/jquery/jquery-2.1.0.min.js"></script>
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
                
               
                $("#reloadData").click(function(){
                    loadData();
                });
                
                
//                //Lista de parámetros que define la configuración de la base de datos
//                var params={
//                    database: "tiendamusical",  //Nombre de la base de datos
//                    version: 19,                //Versión de la base de datos
//                    stores: [
//                        {
//                            name:"music",
//                            key:{keyPath:"song"},
//                            indexes:[           //Lista de índices del almacén, ver parámetros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
//                                {
//                                    name:"artist",
//                                    key:"artist",
//                                    params:{unique: false}
//                                },
//                                {
//                                    name:"song", 
//                                    key:"song", 
//                                    params:{unique: false}
//                                },
//                                {
//                                    name:"album",
//                                    key:"album",
//                                    params:{unique: false}
//                                }
//                            ]
//                        }
//                    ]      
//                };                
//                var database=new Database(params,function(err){
//                    if(err){
//                        console.debug(err);
//                    }else{
//                        //Todo se debe hacer dentro del contexto de la creación de la base de datos
//                        $("#addDefault").click(function(){
//                            var data=[
//                                {artist: "Tom Yorke", song: "Analyse", album: "The eraser"},
//                                {artist: "Bob Marly", song: "One love", album: "Legend"},
//                                {artist: "Alice in Chains", song: "Angry Chair", album: "Unplugged"},
//                                {artist: "Fito Paez", song: "Circo Beat", album: "Circo Beat"},
//                                {artist: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"}
//                            ];
//                            database.add("music",data,function(err){
//                                if(err){console.debug(err);}
//                            });
//                        });
//                        $("#add").click(function(){
//                            var nuevo={
//                                artist:$("#artist").val(),
//                                song:$("#song").val(),
//                                album:$("#album").val()
//                            };
//                            database.add("music",nuevo,function(err){
//                                if(err){console.debug(err);}
//                            });
//                        });
//                        $("#get").click(function(){
//                            var key=$("#keyGet").val();
//                            database.get("music",key,function(err,data){
//                                if(err){
//                                    console.debug(err);
//                                }else{
//                                    if(data.length>0){
//                                        $("#ukey").val(key);
//                                        $("#uartist").val(data[0].artist);
//                                        $("#usong").val(data[0].song);
//                                        $("#ualbum").val(data[0].album);
//                                    }
//                                }
//                            });
//                        });
//                        $("#update").click(function(){
//                            var object={
//                                artist:$("#uartist").val(),
//                                song:$("#usong").val(),
//                                album:$("#ualbum").val()
//                            };
//                            database.update("music",$("#ukey").val(),object,function(err){
//                                if(err){console.debug(err);}
//                            });
//                        });
//                        $("#delete").click(function(){
//                            database.delete("music",$("#key").val(),function(err){
//                                if(err){console.debug(err);}
//                            });
//                        });
//                    }
//                });
                
                
                
                
            });
        </script>
    </head>
    <body>
        <input type="button" id="reloadData" value="Recargar datos"/>
    </body>
</html>
