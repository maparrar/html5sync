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
        debugLevel:0,           //Nivel desde el cuál se debe empezar para la visualización del debug
        debugCrud:false         //Si se deben mostrar los mensajes de debug de las operaciones CRUD
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
            debug("Successful access to database: "+self.params.database+" - version: "+self.version,"good",self.params.debugLevel+1);
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
            debug("Updating database: "+self.params.database,"info",self.params.debugLevel);
            //Se crean los almacenes de datos pasados en los parámetros
            for (var i in self.params.stores) {
                var storeParams = self.params.stores[i];
                //Borra el almacén si existe
                if (self.params.options.overwriteObjectStores) {
                    deleteStore(storeParams.name);
                }
                var store = self.db.createObjectStore(storeParams.name, storeParams.key);
                debug("Object store created: " + storeParams.name,"good",self.params.debugLevel+1);
                //Se crea el conjunto de índices para cada almacén
                for (var j in storeParams.indexes){
                    var indexParams=storeParams.indexes[j];
                    var index = store.createIndex(indexParams.name,indexParams.key,indexParams.params);
                    debug("Index created: " + indexParams.name,"good",self.params.debugLevel+2);
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
            debug("Object store deleted: "+name,"good",self.params.debugLevel+1);
        }catch(e){
            debug("Object store not found: "+name,"bad",self.params.debugLevel+1);
        }
    };
    /**
     * Borra los datos un almacén de objetos. ¡¡¡ Elimina todo el contenido !!! 
     * @param {string} name Nombre del almacén de objetos
     */
    self.clearStore=function(table,callback){
        var storeName=table.name;
        debug("Deleting object store: "+storeName,"info",self.params.debugLevel);
        try{
            var tx = self.db.transaction([storeName],"readwrite");
            var store = tx.objectStore(storeName);
            tx.oncomplete=function(e){
                debug("Object store deleted: "+storeName,"good",self.params.debugLevel+1);
                if(callback)callback(false,table);
            };
            tx.onerror=function(e){
                if(callback)callback(e);
            };
            store.clear();
        }catch(e){
            debug("Object store not found: "+storeName,"bad",self.params.debugLevel+1);
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
        if(self.params.debugCrud)debug("add() - Transaction started","info",self.params.debugLevel+2);
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        //Evento que se dispara cuando se finaliza la transacción con éxito
        tx.oncomplete = function(e) {
            if(callback)callback(false);
            if(self.params.debugCrud)debug("add() - Transaction ended","good",self.params.debugLevel+2);
        };
        //Si es solo un objeto, se crea un array de un objeto para recorrerlo con un ciclo
        if(Object.prototype.toString.call(data)!=="[object Array]"){
            data=new Array(data);
        }
        for (var i in data) {
            var request = store.add(data[i]);
            if(self.params.debugCrud)debug("add()","info",self.params.debugLevel+3);
            request.onerror = function(e) {
                if(self.params.debugCrud)debug("add() - One object violates the unicity. Do not add more objects","bad",self.params.debugLevel+3);
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
        if(self.params.debugCrud)debug("get() - Transaction started","info",self.params.debugLevel+2);
        var tx = self.db.transaction([storeName]);
        var store = tx.objectStore(storeName);
        var range=IDBKeyRange.only(key);
        var output=new Array();
        store.openCursor(range).onerror=function(e){
            if(self.params.debugCrud)debug("get() Couldn't access object with key '"+key+"' in database.","bad",self.params.debugLevel+2);
            if(callback)callback(e.target.error);
        };
        store.openCursor(range).onsuccess = function(e) {
            var cursor = e.target.result;
            if (cursor) {
                output.push(cursor.value);
                cursor.continue();
            }else{
                if(self.params.debugCrud)debug("get() - Transaction ended","good",self.params.debugLevel+2);
                if(callback)callback(false,output);
            }
        };
    };
     /**
     * Retorna un conjunto de objetos de la base de datos
     * @param {string} storeName Nombre del almacÃ©n de datos donde se quiere leer la informaciÃ³n
     * @param {function} callback FunciÃ³n a la que se retornan los resultados
     */
    self.list=function(storeName,callback){
        if(self.params.debugCrud)debug("get() - Transaction started","info",self.params.debugLevel+2);
        var tx = self.db.transaction([storeName]);
        var store = tx.objectStore(storeName);
        var range=IDBKeyRange.lowerBound(0);
        var output=new Array();
        store.openCursor(range).onerror=function(e){
            if(self.params.debugCrud)debug("lis() Couldn't access object with key '"+key+"' in database.","bad",self.params.debugLevel+2);
            if(callback)callback(e.target.error);
        };
        store.openCursor(range).onsuccess = function(e) {
            var cursor = e.target.result;
            if (cursor) {
                output.push(cursor.value);
                cursor.continue();
            }else{
                if(self.params.debugCrud)debug("list() - Transaction ended","good",self.params.debugLevel+2);
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
        if(self.params.debugCrud)debug("upd() - Transaction started","info",self.params.debugLevel+2);
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
                if(self.params.debugCrud)debug("upd() - Cannot update the object: "+key,"bad",self.params.debugLevel+2);
                if(callback)callback(e.target.error);
            };
            requestUpdate.onsuccess = function(e) {
                if(self.params.debugCrud)debug("upd() - Transaction ended","good",self.params.debugLevel+2);
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
        if(self.params.debugCrud)debug("del() - Transaction started","info",self.params.debugLevel+2);
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        var request = store.delete(key);
        request.onerror = function(e) {
            if(self.params.debugCrud)debug("del() - Cannot delete the object: "+key,"bad",self.params.debugLevel+2);
            if(callback)callback(e.target.error);
        };
        request.onsuccess = function(e) {
            if(self.params.debugCrud)debug("del() - Transaction ended","good",self.params.debugLevel+2);
        };
    };
    /**************************************************************************/
    /*************************** HTML5SYNC METHODS ****************************/
    /**************************************************************************/
    /**
     * Recibe una tabla formateada del servidor, la formatea para el navegador y agrega
     * los registros
     * @param {object} page Página de una tablaTabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
    self.addPageToTable=function(page,callback){
        var rows=serverTableToJSON(page);
        self.add(page.name,rows,function(err){
            if(err){
                if(callback)callback(err);
            }else{
                if(callback)callback(false);
            }
        });
    };
    /**
     * Recibe una tabla del servidor y la modifica para que se puedan ingresar 
     * en la base de datos del navegador. Asocia a cada dato de cada registro 
     * con el nombre de la columna. Retorna un JSON bien formado
     * @param {string} table Tabla proveniente del servidor en JSON
     * @returns {object} La tabla en JSON bien formada
     */
    function serverTableToJSON(table){
        var rows=table.data;
        var fields=table.fields;
        var registers=new Array();
        for(var i in rows){
            var row=rows[i];
            var register=new Object();
            for(var j in row){
                register[fields[j].name]=row[j];
            }
            registers.push(register);
        }
        return registers;
    };
};

/******************************************************************************/
/******************************* STATIC METHODS *******************************/
/******************************************************************************/
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
    var prerequest = window.indexedDB.open(name);
    prerequest.onsuccess = function(e) {
        var db = prerequest.result;
        db.close();
        var request=window.indexedDB.deleteDatabase(name);
        debug("Trying to delete database: "+name,"info",debugLevel);
        debug("THIS MAY TAKE A WHILE","wait",debugLevel);
        request.onsuccess = function () {
            debug("Database deleted: "+name,"good",debugLevel+1);
            if(callback)callback(false);
        };
        request.onerror = function () {
            debug("Cannot delete database: "+name,"bad",debugLevel+1);
            if(callback)callback(new Error("Cannot delete database: "+name,"bad"));
        };
   };
   prerequest.onerror = function(e) {
       callback(new Error("Database "+name+" not found"));
   };
};
/**
* Función que convierte un conjunto de tablas JSON en almacenes de objetos
* @param {string} tables Conjunto de Table en JSON
* @returns {string} Almacén de objetos en JSON
*/
Database.tablesToStores=function(tables){
    var stores=new Array();
    for(var i in tables){
        stores.push(Database.tableToStore(tables[i]));
    }
    return stores;
};
/**
* Función que convierte una tabla que está en formato JSON a un almacén de objetos
* @param {string} table Tabla en JSON
* @returns {string} Almacén de objetos en JSON
*/
Database.tableToStore=function(table){
    var indexes=new Array();
    var key={autoIncrement : true};
    for(var i in table.fields){
        var field=table.fields[i];
        var unique=false;
        if(field.key==="PK"){
            unique=true;
            key={keyPath:field.name};
        }
        var index={
            name:field.name,
            key:field.name,
            params:{unique: unique}
        };
        indexes.push(index);
    }
    var store={
        name:table.name,
        key:key,
        indexes:indexes
    };
    return store;
};