<!doctype html>
<html>
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="scripts/base.css">
        <script src="scripts/jquery-2.1.0.min.js"></script>
        <script src="source/Database.js"></script>
        <script type="text/javascript">
            $( document ).ready(function(){
                
                //Create the exist() function for any selector. i.e: $("selector").exist()
                $.fn.exist=function(){return this.length>0;};
                window.debugging=true;
                window.debug=function(message){
                    if(window.debugging){
                        if(!$("#html5sync_debug").exist()){
                            $("body").prepend('<div id="html5sync_debug"></div>');
                        }
                        $("#html5sync_debug").append(message+"<br>");
                        $("#html5sync_debug").scrollTop($('#html5sync_debug').get(0).scrollHeight);
                    }
                };                
                
                //Lista de parámetros que define la configuración de la base de datos
                var params={
                    database: "tiendamusical",         //Nombre de la base de datos
                    stores: [
                        {
                            name:"music",
                            key:{autoIncrement:true},
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
                var data=[
                    {artist: "Tom Yorke", song: "Analyse", album: "The eraser"},
                    {artist: "Bob Marly", song: "One love", album: "Legend"},
                    {artist: "Alice in Chains", song: "Angry Chair", album: "Unplugged"},
                    {artist: "Fito Paez", song: "Circo Beat", album: "Circo Beat"},
                    {artist: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"}
                ];
                var database=new Database(params,function(){
                    database.add("music",data);
                });
                
                
                
                $("#add").click(function(){
                    var nuevo={
                        artist:$("#artist").val(),
                        song:$("#song").val(),
                        album:$("#album").val()
                    };
                    database.add("music",nuevo);
                });
                $("#delete").click(function(){
                    database.delete("music",$("#key").val());
                });
                $("#get").click(function(){
                    var key=parseInt($("#keyGet").val());
                    if(!isNaN(key)){
                        database.get("music",key,function(data){
                            console.debug(data);
                        });
                    }
                });
            });
        </script>
    </head>
    <body>
        <input type="text" id="artist" placeholder="Artista"/>
        <input type="text" id="song" placeholder="Canción"/>
        <input type="text" id="album" placeholder="Álbum"/>
        <button id="add">Agregar</button>
        <br>
        <input type="text" id="key" placeholder="Clave del objeto a eliminar"/>
        <button id="delete">Eliminar</button>
        <br>
        <input type="text" id="keyGet" placeholder="Clave del objeto a obtener"/>
        <button id="get">Retornar</button>
    </body>
</html>
