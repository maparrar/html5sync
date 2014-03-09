<!--
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
 -->
 
 <?php
 
 
 
// $sqlite=new SQLite3("state");
//    
//    print_r($sqlite);
    
    
//    include_once 'html5sync/server/core/Connection.php';
//    include_once 'html5sync/server/core/Database.php';
//    include_once 'html5sync/server/core/Field.php';
//    include_once 'html5sync/server/core/Table.php';
//    
//    include_once 'html5sync/server/dao/DaoTable.php';
//    
//    
//    
//    
//    //Se leen las variables de configuración
//    $config=require_once 'html5sync/server/config.php';
//    $tables=$config["tables"];
//    
//    
//    //Se crea una instancia de la base de datos con la conexión (read+write)
//    $db=new Database(
//            $config["database"]["name"],
//            $config["database"]["driver"],
//            $config["database"]["host"], 
//            new Connection(
//                "all",
//                $config["database"]["login"],
//                $config["database"]["password"]
//            )
//        );
//    
//    //Se crea el objeto para manejar tablas con PDO
//    $dao=new DaoTable($db);
//    
//    //TODO: Filtrar los tipos de datos
//    
//    
//    //Se lee cada tabla
//    foreach ($tables as $tabledata) {
//        $table=$dao->loadTable($tabledata["name"],$tabledata["mode"]);
//        
//        
//        print_r($table->jsonEncode());
//    }
 ?>
 
<!doctype html>
<html>
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
                
                
                //Lista de parámetros que define la configuración de la base de datos
                var params={
                    database: "tiendamusical",  //Nombre de la base de datos
                    version: 19,                //Versión de la base de datos
                    stores: [
                        {
                            name:"music",
                            key:{keyPath:"song"},
                            indexes:[           //Lista de índices del almacén, ver parámetros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
                                {
                                    name:"artist",
                                    key:"artist",
                                    params:{unique: false}
                                },
                                {
                                    name:"song", 
                                    key:"song", 
                                    params:{unique: false}
                                },
                                {
                                    name:"album",
                                    key:"album",
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
                        //Todo se debe hacer dentro del contexto de la creación de la base de datos
                        $("#addDefault").click(function(){
                            var data=[
                                {artist: "Tom Yorke", song: "Analyse", album: "The eraser"},
                                {artist: "Bob Marly", song: "One love", album: "Legend"},
                                {artist: "Alice in Chains", song: "Angry Chair", album: "Unplugged"},
                                {artist: "Fito Paez", song: "Circo Beat", album: "Circo Beat"},
                                {artist: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"}
                            ];
                            database.add("music",data,function(err){
                                if(err){console.debug(err);}
                            });
                        });
                        $("#add").click(function(){
                            var nuevo={
                                artist:$("#artist").val(),
                                song:$("#song").val(),
                                album:$("#album").val()
                            };
                            database.add("music",nuevo,function(err){
                                if(err){console.debug(err);}
                            });
                        });
                        $("#get").click(function(){
                            var key=$("#keyGet").val();
                            database.get("music",key,function(err,data){
                                if(err){
                                    console.debug(err);
                                }else{
                                    if(data.length>0){
                                        $("#ukey").val(key);
                                        $("#uartist").val(data[0].artist);
                                        $("#usong").val(data[0].song);
                                        $("#ualbum").val(data[0].album);
                                    }
                                }
                            });
                        });
                        $("#update").click(function(){
                            var object={
                                artist:$("#uartist").val(),
                                song:$("#usong").val(),
                                album:$("#ualbum").val()
                            };
                            database.update("music",$("#ukey").val(),object,function(err){
                                if(err){console.debug(err);}
                            });
                        });
                        $("#delete").click(function(){
                            database.delete("music",$("#key").val(),function(err){
                                if(err){console.debug(err);}
                            });
                        });
                    }
                });
                
                
                
                
            });
        </script>
    </head>
    <body>
        
        <input type="button" id="checkChanges" value="verificar cambios"/>
        <input type="button" id="loadData" value="cargar datos"/>
        
        
        <h3>Agregar el siguiente conjunto de datos predefinidos a la base de datos</h3>
        var data=[<br>
        {artist: "Tom Yorke", song: "Analyse", album: "The eraser"},<br>
        {artist: "Bob Marly", song: "One love", album: "Legend"},<br>
        {artist: "Alice in Chains", song: "Angry Chair", album: "Unplugged"},<br>
        {artist: "Fito Paez", song: "Circo Beat", album: "Circo Beat"},<br>
        {artist: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"}<br>
        ];<br>
        <button id="addDefault">Agregar predefinidos</button>
        <br><hr>
        
        <h3>Agregar un registro a la base de datos</h3>
        <input type="text" id="artist" placeholder="Artista"/>
        <input type="text" id="song" placeholder="Canción"/>
        <input type="text" id="album" placeholder="Álbum"/>
        <button id="add">Agregar</button>
        <br><hr>
        
        <h3>Eliminar un registro de la base de datos</h3>
        <input type="text" id="key" placeholder="Clave del objeto a eliminar"/>
        <button id="delete">Eliminar</button>
        <br><hr>
        
        <h3>Retornar un registro de la base de datos</h3>
        <input type="text" id="keyGet" placeholder="Clave del objeto a obtener"/>
        <button id="get">Retornar</button>
        <br><hr>
        
        <h3>Actualizar un registro de la base de datos</h3>
        <input type="text" id="ukey" placeholder="Clave del objeto a actualizar"/>
        <input type="text" id="uartist" placeholder="Artista"/>
        <input type="text" id="usong" placeholder="Canción"/>
        <input type="text" id="ualbum" placeholder="Álbum"/>
        <button id="update">Actualizar</button>
        <br><hr>
    </body>
</html>
