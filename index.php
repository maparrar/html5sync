<html>
    <head>
        <title>html5sync</title>
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
                
                
                
//                
//                
//                
//                
//                if(window.indexedDB === undefined) {
//                    console.log("Este navegador no soporta indexedDB");
//                }else{
//                    debug("Base de datos indexedDB soportada");
//                    
//                    
//                    
//                    
//                    var db;
//                    var request = window.indexedDB.open("testing",1);
//                    /*
//                     * Evento del request para manejar los errores. Se dispara si
//                     * por ejemplo, un usuario no permite que se usen bases de
//                     * datos indexedDB en el navegador.
//                     */
//                    request.onerror = function(e) {
//                        debug("No es posible conectar con la base de datos local");
//                    };
//                    /*
//                     * Evento del request cuando es posible usar la base de datos
//                     * indexedDB en el navegador.
//                     */
//                    request.onsuccess = function(e) {
//                        debug("Request completo a la base de datos");
//                        db = request.result;
//                    };
//                    
//                    /*
//                     * Evento del request que se dispara cuando la base de datos
//                     * necesita ser modificada (la estructura de la base de datos).
//                     * Si en la línea:
//                     *      var request = window.indexedDB.open("html5sync",3);
//                     * se cambia la versión, es decir el segundo parámetro de la
//                     * función open() a 4, se dispara este evento.
//                     * Si se hace un downgrade, se pone en 2, genera un error.
//                     */
//                    request.onupgradeneeded = function(e) {
//                        //Se crea o reemplaza la base de datos
//                        db = e.target.result;
//                        
//                        /*
//                         * Crea un almacén de objetos que se puede definir de la
//                         * siguiente manera:
//                         * |Key Path    Key Generator 	Description
//                         * |(keyPath)   (autoIncrement)
//                         * |__________________________________________________________________________________
//                         *  No          No              This object store can hold any kind of value, 
//                         *                              even primitive values like numbers and strings. 
//                         *                              You must supply a separate key argument whenever 
//                         *                              you want to add a new value.
//                         *  Yes     	No              This object store can only hold JavaScript objects. 
//                         *                              The objects must have a property with the same name 
//                         *                              as the key path.
//                         *  No          Yes             This object store can hold any kind of value. The 
//                         *                              key is generated for you automatically, or you can 
//                         *                              supply a separate key argument if you want to use a 
//                         *                              specific key.
//                         *  Yes         Yes             This object store can only hold JavaScript objects. 
//                         *                              Usually a key is generated and the value of the 
//                         *                              generated key is stored in the object in a property 
//                         *                              with the same name as the key path. However, if such 
//                         *                              a property already exists, the value of that property 
//                         *                              is used as key rather than generating a new key.
//                         */
////                        var store = db.createObjectStore("music", {keyPath: "id"});
//                        var store = db.createObjectStore("music", {autoIncrement : true});
//                        
//                        
//                        /*
//                         * Se crea el conjunto de índices para la base de datos
//                         */
//                        var songIndex = store.createIndex("by_song", "song", {unique: true});
//                        var interpreterIndex = store.createIndex("by_interpreter", "interpreter", { unique: false });
//                        var albumIndex = store.createIndex("by_album", "album", { unique: false });
//                        debug("&Iacute;ndices creados");
//                        
//                        /*
//                         * Este evento se ejecuta cuando se ha creado el almacén
//                         * de objetos. Se usa para agregar de manera segura los 
//                         * datos.
//                         */
//                        store.transaction.oncomplete = function(e) {
//                            //Se crea una transacción para leer y escribir
//                            var tx = db.transaction("music", "readwrite");
//                            //Se obtiene el almacén de objetos
//                            var store = tx.objectStore("music");
//                            
//                            
//                            store.add({interpreter: "Tom Yorke", song: "Analyse", album: "The eraser"});
//                            store.add({interpreter: "Bob Marly", song: "One love", album: "Legend"});
//                            store.add({interpreter: "Alice in Chains", song: "Angry Chair", album: "Unplugged"});
//                            
//                            debug("Informaci&oacute;n de prueba inicial insertada");
//                            
//                        };
//
//                        
//
//                        db.onerror = function(event) {
//                            // Error en el acceso a la base de datos
//                            debug("Error de base de datos: " + event.target.errorCode);
//                        };
//                    };
//
//
//
//                    
//                    
//                    
//                    
//                    $("#write").click(function(){
//                        var tx = db.transaction(["music"], "readwrite");
//                        // Do something when all the data is added to the database.
//                        tx.oncomplete = function(e) {
//                            debug("Transacci&oacute;n finalizada");
//                        };
//
//                        tx.onerror = function(e) {
//                          // Don't forget to handle errors!
//                        };
//
//                        var store = tx.objectStore("music");
//                        
//                        var objects=[
//                            {interpreter: "Fito Paez", song: "Circo Beat", album: "Circo Beat"},
//                            {interpreter: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"}
//                        ];
//                        
//                        for (var i in objects) {
//                            var request = store.add(objects[i]);
//                            request.onerror = function(e){
//                                debug("Error ingresando: "+JSON.stringify(objects[i])+" .::. "+request.error);
//                            };
//                        }
//                    });
//                    
//                    
//                }
//
//
//
//
//
//
////                        store.put({id: 4, interpreter: "Fito Paez", song: "Circo Beat", album: "Circo Beat"});
////                        store.put({id: 5, interpreter: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"});
//
//                
//
//
//
//
//
//
//
//
//
//
//
//
//
//



                
                
            });
        </script>
    </head>
    <body>
        <button id="write">Escribir</button>
    </body>
</html>
