/*
 * Clase para el manejo de bases de datos
 * @param {object} params Objeto con los parámetros de la base de datos en el navegador:
 *      params={
 *          database: "html5db"         //Nombre de la base de datos
 *          stores: [
 *              {
 *                  name:"store1",
 *                  key:{keyPath: "key_name1"},
 *                  indexes:[           //Lista de índices del almacén, ver parámetros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
 *                      {"index_name_1", "key_name1", {unique: true}},
 *                      {"index_name_2", "key_name2", {unique: false}},
 *                      {"index_name_3", "key_name3", {unique: false}}
 *                  ],
 *              },
 *              {
 *                  name:"store2",
 *                  key:{autoIncrement : true},
 *                  indexes:[           //Lista de índices del almacén
 *                      {"index_name_1", "key_name1", {unique: true}},
 *                      {"index_name_2", "key_name2", {unique: false}},
 *                      {"index_name_3", "key_name3", {unique: false}}
 *                  ],
 *              },
 *              {
 *                  name:"store3",
 *                  key:{autoIncrement : true},
 *                  indexes:[           //Lista de índices del almacén
 *                      {"index_name_1", "key_name1", {unique: true}},
 *                      {"index_name_2", "key_name2", {unique: false}},
 *                      {"index_name_3", "key_name3", {unique: false}}
 *                  ],
 *              }
 *          ]      
 *      }
 */
function Database(params){
    var self=this;
    self.db;
    self.request = window.indexedDB.open("testing",1);
    
    
    //Variables por defecto
    var def = {
        database: "html5sync",
    };
    self = $.extend(def,params);
    
    console.debug(self);
    
    self.setEvents=function(){
        
    };
    
    
    /*
     * Evento del request para manejar los errores. Se dispara si
     * por ejemplo, un usuario no permite que se usen bases de
     * datos indexedDB en el navegador.
     */
    request.onerror = function(e) {
        debug("No es posible conectar con la base de datos local");
    };
    /*
     * Evento del request cuando es posible usar la base de datos
     * indexedDB en el navegador.
     */
    request.onsuccess = function(e) {
        debug("Request completo a la base de datos");
        db = request.result;
    };

    /*
     * Evento del request que se dispara cuando la base de datos
     * necesita ser modificada (la estructura de la base de datos).
     * Si en la línea:
     *      var request = window.indexedDB.open("html5sync",3);
     * se cambia la versión, es decir el segundo parámetro de la
     * función open() a 4, se dispara este evento.
     * Si se hace un downgrade, se pone en 2, genera un error.
     */
    request.onupgradeneeded = function(e) {
        //Se crea o reemplaza la base de datos
        db = e.target.result;

        /*
         * Crea un almacén de objetos que se puede definir de la
         * siguiente manera:
         * |Key Path    Key Generator 	Description
         * |(keyPath)   (autoIncrement)
         * |__________________________________________________________________________________
         *  No          No              This object store can hold any kind of value, 
         *                              even primitive values like numbers and strings. 
         *                              You must supply a separate key argument whenever 
         *                              you want to add a new value.
         *  Yes     	No              This object store can only hold JavaScript objects. 
         *                              The objects must have a property with the same name 
         *                              as the key path.
         *  No          Yes             This object store can hold any kind of value. The 
         *                              key is generated for you automatically, or you can 
         *                              supply a separate key argument if you want to use a 
         *                              specific key.
         *  Yes         Yes             This object store can only hold JavaScript objects. 
         *                              Usually a key is generated and the value of the 
         *                              generated key is stored in the object in a property 
         *                              with the same name as the key path. However, if such 
         *                              a property already exists, the value of that property 
         *                              is used as key rather than generating a new key.
         */
//                        var store = db.createObjectStore("music", {keyPath: "id"});
        var store = db.createObjectStore("music", {autoIncrement : true});


        /*
         * Se crea el conjunto de índices para la base de datos
         */
        var songIndex = store.createIndex("by_song", "song", {unique: true});
        var interpreterIndex = store.createIndex("by_interpreter", "interpreter", { unique: false });
        var albumIndex = store.createIndex("by_album", "album", { unique: false });
        debug("&Iacute;ndices creados");

        /*
         * Este evento se ejecuta cuando se ha creado el almacén
         * de objetos. Se usa para agregar de manera segura los 
         * datos.
         */
        store.transaction.oncomplete = function(e) {
            //Se crea una transacción para leer y escribir
            var tx = db.transaction("music", "readwrite");
            //Se obtiene el almacén de objetos
            var store = tx.objectStore("music");


            store.add({interpreter: "Tom Yorke", song: "Analyse", album: "The eraser"});
            store.add({interpreter: "Bob Marly", song: "One love", album: "Legend"});
            store.add({interpreter: "Alice in Chains", song: "Angry Chair", album: "Unplugged"});

            debug("Informaci&oacute;n de prueba inicial insertada");

        };



        db.onerror = function(event) {
            // Error en el acceso a la base de datos
            debug("Error de base de datos: " + event.target.errorCode);
        };
    };
    
    self.varA = 10;
    self.varB = 20;
    self.functionA = function (var1, var2) {
        console.log(var1 + " " + var2);
    };
};