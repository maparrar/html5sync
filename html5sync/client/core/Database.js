/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para el manejo de bases de datos [constructor pattern]
 * @param {object} params Objeto con los parámetros de la base de datos en el navegador:
 *      params={
 *          database: "html5db",         //Nombre de la base de datos
 *          stores: [
 *              {
 *                  name:"store1",
 *                  key:{keyPath: "key_name1"},
 *                  indexes:[           //Lista de índices del almacén, ver parámetros en: https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore.createIndex
 *                      {
 *                          name:"index_name_1", 
 *                          key:"key_name1", 
 *                          params:{unique: true}
 *                      },
 *                      {
 *                          name:"index_name_2",
 *                          key:"key_name2",
 *                          params:{unique: false}
 *                      },
 *                      {
 *                          name:"index_name_3",
 *                          key:"key_name3",
 *                          params:{unique: false}
 *                      }
 *                  ],
 *              },
 *              {
 *                  name:"store2",
 *                  key:{autoIncrement : true},
 *                  indexes:[           //Lista de índices del almacén
 *                      {
 *                          name:"index_name_1", 
 *                          key:"key_name1", 
 *                          params:{unique: true}
 *                      },
 *                      {
 *                          name:"index_name_2",
 *                          key:"key_name2",
 *                          params:{unique: false}
 *                      },
 *                      {
 *                          name:"index_name_3",
 *                          key:"key_name3",
 *                          params:{unique: false}
 *                      }
 *                  ]
 *              }
 *          ],
 *          options:{
 *              overwriteObjectStores:true,     //(default) Elimina los almacenes anteriores y los sobreescribe
 *          }      
 *      };
 * @param {function} callback Función que garantiza que en su contexto ya se ha cargado la base de datos
 */
var Database = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.db;                //Base de datos indexedDB
    self.request;           //Objeto que contiene la conexión a la base de datos
    self.callback=callback; //Función que garantiza que en su contexto ya se ha cargado la base de datos
    
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        database: "html5db",
        load: false,    //True si solo se debe cargar la base de datos (no crear)
        version: 1,
        options: {
            overwriteObjectStores: true
        },
        debugLevel:0    //Nivel desde el cuál se debe empezar para la visualización del debug
    };
    self.params = $.extend(def, params);
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Database = function() {
        if(window.indexedDB !== undefined) {
            if(self.params.load){   //Si solo se debe cargar la base de datos (no crear)
                self.request = window.indexedDB.open(self.params.database);
            }else{
                self.version = self.params.version; //Versión de la Base de datos indexedDB
                self.request = window.indexedDB.open(self.params.database, self.version);
            }
            debug("Accessing database: "+self.params.database,"info",self.params.debugLevel);
            //Asigna los eventos
            events();
        }else{
            self.callback(new Error("This browser does not support indexedDB"));
        }
    }();
    
    /**************************************************************************/
    /**************************** PRIVATE METHODS *****************************/
    /**************************************************************************/
    /**
     * Método privado que asigna funciones a los eventos
     */
    function events() {
        /*
         * Evento del request para manejar los errores. Se dispara si
         * por ejemplo, un usuario no permite que se usen bases de
         * datos indexedDB en el navegador.
         */
        self.request.onerror = function(e) {
            self.callback(new Error("Unable to connect to the local database"));
        };
        /*
         * Evento del request cuando es posible usar la base de datos
         * indexedDB en el navegador.
         */
        self.request.onsuccess = function(e) {
            self.db = self.request.result;
            if(self.params.load){
                self.version=self.request.result.version;
            }
            debug("Successful access to the database: "+self.params.database+" - version: "+self.version,"good",self.params.debugLevel+1);
            self.callback(false);
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
        self.request.onupgradeneeded = function(e) {
            //Se crea o reemplaza la base de datos
            self.db = e.target.result;
            debug("Actualizando la estructura de la base de datos: "+self.params.database);
            //Se crean los almacenes de datos pasados en los parámetros
            for (var i in self.params.stores) {
                var storeParams = self.params.stores[i];
                //Borra el almacén si existe
                if (self.params.options.overwriteObjectStores) {
                    deleteStore(storeParams.name);
                }
                var store = self.db.createObjectStore(storeParams.name, storeParams.key);
                debug("... Se ha creado el almacén de datos: " + storeParams.name);
                //Se crea el conjunto de índices para cada almacén
                for (var j in storeParams.indexes){
                    var indexParams=storeParams.indexes[j];
                    var index = store.createIndex(indexParams.name,indexParams.key,indexParams.params);
                    debug("... ... Se ha creado el índice: " + indexParams.name);
                }
            }
        };
    };
    /**
     * Borra un almacén de objetos. ¡¡¡ Elimina todo el contenido !!! 
     * @param {string} name Nombre del almacén de objetos
     */
    function deleteStore(name){
        try{
            self.db.deleteObjectStore(name);
            debug("... Se eliminó con éxito el almacén: "+name);
        }catch(e){
            debug("... No hay versión anterior del almacén: "+name);
        }
    };
    /**
     * Borra los datos un almacén de objetos. ¡¡¡ Elimina todo el contenido !!! 
     * @param {string} name Nombre del almacén de objetos
     */
    self.clearStore=function(table,callback){
        var storeName=table.name;
        debug("Iniciando borrado de almacén: "+storeName);
        try{
            var tx = self.db.transaction([storeName],"readwrite");
            var store = tx.objectStore(storeName);
            tx.oncomplete=function(e){
                debug("Fin borrado de almacén: "+storeName);
                if(callback)callback(false,table);
            };
            tx.onerror=function(e){
                if(callback)callback(e);
            };
            store.clear();
        }catch(e){
            debug("... No hay versión anterior del almacén: "+storeName);
        }
    };
    
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /**
     * Inserta objetos en un almacén. Recibe un objeto o un array de objetos
     * @param {string} storeName Nombre del almacén de datos donde se quiere insertar la información
     * @param {object[]} data Objeto o array de objetos
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.add=function(storeName,data,callback){
//        debug("add() - Transacción iniciada");
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        //Evento que se dispara cuando se finaliza la transacción con éxito
        tx.oncomplete = function(e) {
            if(callback)callback(false);
//            debug("... add() - Transacción finalizada");
        };
        //Si es solo un objeto, se crea un array de un objeto para recorrerlo con un ciclo
        if(Object.prototype.toString.call(data)!=="[object Array]"){
            data=new Array(data);
        }
        for (var i in data) {
            var request = store.add(data[i]);
//            debug("... add()");
            request.onerror = function(e) {
//                debug("... add() ... Error: Uno o más objetos violan la unicidad de alguno de los índices. No se agregarán más objetos.");
                if(callback)callback(e.target.error);
            };
        }
    };
    /**
     * Retorna un conjunto de objetos de la base de datos
     * @param {string} storeName Nombre del almacén de datos donde se quiere insertar la información
     * @param {mixed} key
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.get=function(storeName,key,callback){
        debug("get() - Transaction started","info",self.params.debugLevel+2);
        var tx = self.db.transaction([storeName]);
        var store = tx.objectStore(storeName);
        var range=IDBKeyRange.only(key);
        var output=new Array();
        store.openCursor(range).onerror=function(e){
            debug("get() Couldn't access object with key '"+key+"' in database.","bad",self.params.debugLevel+2);
            if(callback)callback(e.target.error);
        };
        store.openCursor(range).onsuccess = function(e) {
            var cursor = e.target.result;
            if (cursor) {
                output.push(cursor.value);
                cursor.continue();
            }else{
                debug("get() - Transaction ended","good",self.params.debugLevel+2);
                if(callback)callback(false,output);
            }
        };
    };
    /**
     * Actualiza un objeto de la base de datos a partir del almacén y la clave.
     * Si dentro del objeto pasado como parámetro se actualiza key, y esta no 
     * existe, se crea un nuevo objeto.
     * @param {string} storeName Nombre del almacén de datos
     * @param {mixed} key Clave del objeto
     * @param {object} object Objeto con las actualizaciones
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.update=function(storeName,key,object,callback){
//        debug("upd() - Transacción iniciada");
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        var request = store.get(key);
        request.onerror = function(e) {
            self.add(storeName,object,function(err){
                if(callback)callback(err);
            });
        };
        request.onsuccess = function(e) {
            // Retorna la versión anterior del objeto y lo actualiza con el nuevo
            var older = request.result;
            var newer=$.extend(older,object);
            // Vuelve a insertar el objeto en la base de datos
            var requestUpdate = store.put(newer);
            requestUpdate.onerror = function(e) {
//                debug("... upd() ... No se pudo actualizar el objeto: "+key);
                if(callback)callback(e.target.error);
            };
            requestUpdate.onsuccess = function(e) {
//                debug("... upd() ... Transacción finalizada");
                if(callback)callback(false,newer);
            };
        };
    };
    /**
     * Elimina objetos de la base de datos a partir de su clave y el almacén
     * @param {string} storeName Nombre del almacén de datos
     * @param {mixed} key 
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.delete=function(storeName,key,callback){
        debug("del() - Transacción iniciada");
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        var request = store.delete(key);
        request.onerror = function(e) {
            debug("... del() ... No se pudo eliminar el objeto: "+key);
            if(callback)callback(e.target.error);
        };
        request.onsuccess = function(e) {
            debug("... del() ... Transacción finalizada");
        };
    };
};
/**
* Static method. Check if a database exists
* @param {string} name Database name
* @param {function} callback Function to return the response
* @returns {bool} True if the database exists
*/
Database.databaseExists=function(name,callback){
   var dbExists = true;
   var request = window.indexedDB.open(name);
   request.onupgradeneeded = function (e){
       if(request.result.version===1){
           dbExists = false;
           window.indexedDB.deleteDatabase(name);
           if(callback)
               callback(dbExists);
       }
   };
   request.onsuccess = function(e) {
       if(dbExists){
           if(callback)
                   callback(dbExists);
       }
   };
};
/**
* Static method. Return a database (the database must exists)
* @param {string} name Database name
* @param {function} callback Function to return the response
* @param {int} debugLevel Nivel de debug
*/
Database.loadDatabase=function(name,callback,debugLevel){
    if(!debugLevel)debugLevel=0;
   var request = window.indexedDB.open(name);
   request.onerror = function(e) {
       callback(new Error("Unable to connect to the local database "+name));
   };
   request.onsuccess = function(e) {
        var database=new Database({load:true,database:name,debugLevel:debugLevel},function(err){
            if(err){
                if(callback)callback(err);
            }else{
                if(callback)callback(false,database);
            }
        });
   };
};
/**
* Borra una base de datos indexedDB ¡¡¡ Elimina todo el contenido !!! 
* @param {string} name Nombre de la base de datos
* @param {function} callback Function to return the response
* @param {int} debugLevel Nivel de debug
*/
Database.deleteDatabase=function(name,callback,debugLevel){
    if(!debugLevel)debugLevel=0;
    var request=window.indexedDB.deleteDatabase(name);
    request.onsuccess = function () {
        debug("Database deleted: "+name,"good",debugLevel);
        if(callback)callback(false);
    };
    request.onerror = function () {
        debug("Cannot delete database: "+name,"bad",debugLevel);
        if(callback)callback(false);
    };
};