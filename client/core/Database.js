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
    self.structTables=false;      //Almacena la estructura de las tablas de la base de datos de configuración
    
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        database: "html5db",
        load: false,    //True si solo se debe cargar la base de datos (no crear)
        version: 1,
        storeTransactions:false,    //True si se deben almacenar las transacciones realizadas en la base de datos
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
            debug("Error creating database: "+self.params.database,"bad");
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
    self.clearStore=function(tableName,callback){
        var storeName=tableName;
        debug("Deleting object store: "+storeName,"info",self.params.debugLevel);
        try{
            var tx = self.db.transaction([storeName],"readwrite");
            var store = tx.objectStore(storeName);
            tx.oncomplete=function(e){
                debug("Object store deleted: "+storeName,"good",self.params.debugLevel+1);
                if(callback)callback(false,storeName);
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
    /****************************** CRUD METHODS ******************************/
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
        var indexes=false;
        var counter=0;
        //Evento que se dispara cuando se finaliza la transacción con éxito
        tx.oncomplete = function(e) {
            if(self.params.debugCrud)debug("add() - Transaction ended","good",self.params.debugLevel+2);
        };
        //Si es solo un objeto, se crea un array de un objeto para recorrerlo con un ciclo
        if(Object.prototype.toString.call(data)!=="[object Array]"){
            data=new Array(data);
        }else{
            indexes=new Array();
        }
        for (var i in data) {
            
            //Si es una tabla con autoincrement, se inserta aumentando la PK
            
            //sino, se verifica que la PK no exista antes en la base de datos
            
            
            //Se verifica cada campo para establecer su tipo
            for(var columnName in data[i]){
                var struct=structColumn(storeName,columnName);
                if(struct){
                    if(struct.type==="int"){
                        data[i][columnName]=parseInt(data[i][columnName]);
                    }else if(struct.type==="double"){
                        data[i][columnName]=parseFloat(data[i][columnName]);
                    }
                }
            }
            var request = store.add(data[i]);
            if(self.params.debugCrud)debug("add()","info",self.params.debugLevel+3);
            request.onerror = function(e) {
                if(self.params.debugCrud)debug("add() - One object violates the unicity. Do not add more objects","bad",self.params.debugLevel+3);
                if(callback)callback(e.target.error);
            };
            request.onsuccess=function(e){
                counter++;
                var index=e.target.result;
                if(!indexes){
                    indexes=index;
                }else{
                    indexes.push(index);
                }
                if(parseInt(counter)===parseInt(data.length)){
                    if(callback)callback(false,indexes);
                }
                //Se agrega cada uno de los objetos agregados a la tabla de transacciones
                if(self.params.storeTransactions){
                    var pk=getPkFromTable(storeName);
                    var row=data[i];
                    row[pk.name]=index;
                    var transaction={
                        table:storeName,
                        key:index,
                        date:now(),
                        transaction:"INSERT",
                        row:row
                    };
                    self.configurator.db.add("Transactions",transaction,function(err){
                        if(err){
                            if(self.params.debugCrud)debug("Add inserted to transactions failed","bad",self.params.debugLevel+3);
                        }else{
                            if(self.params.debugCrud)debug("Add inserted to transactions success","good",self.params.debugLevel+3);
                        }
                    });
                }
            };
        }
    };
    /**
     * Retorna un objeto o conjunto de objetos de la base de datos
     * @param {string} storeName Nombre del almacén de datos donde se quiere insertar la información
     * @param {mixed} columnName Nombre de la columna por la que se busca, si es false, se busca por la PK de la tabla
     * @param {mixed} value Valor que se quiere buscar en la columna
     * @param {function} callback Función a la que se retornan los resultados
     * @return {object[]} Retorna un array de objetos con los que coincidan con la búsqueda
     */
    self.get=function(storeName,columnName,value,callback){
        if(self.params.debugCrud)debug("get() - Transaction started","info",self.params.debugLevel+2);
        var output=new Array();
            var tx = self.db.transaction([storeName]);
            var store = tx.objectStore(storeName);
            if(!columnName){
                var request = store.get(value);
                request.onerror = function(e) {
                    if(callback)callback(e);
                };
                request.onsuccess = function() {
                    var output=new Array();
                    if(request.result!==undefined){
                        output.push(request.result);
                    }
                    if(callback)callback(false,output);
                };
            }else{
                if(self.indexExist(storeName,columnName)){
                    var index = store.index(columnName);
                    var range = IDBKeyRange.only(value);
                    index.openCursor(range).onsuccess = function(e) {
                        var cursor = e.target.result;
                        if (cursor) {
                            output.push(cursor.value);
                            cursor.continue();
                        }else{
                            if(self.params.debugCrud)debug("get() - Transaction ended","good",self.params.debugLevel+2);
                            //Si es solo un objeto, lo retorna como elemento, no como matriz
                            if(output.length===1)output=output[0];
                            if(callback)callback(false,output);
                        }
                    };
                }else{
                    if(self.params.debugCrud)debug("get() - Column name "+columnName+" does not exist","bad",self.params.debugLevel+2);
                    if(callback)callback(new Error("Column name '"+columnName+"' does not exist"));
                }
            }
    };
    /**
     * Actualiza un objeto de la base de datos a partir del almacén y la clave.
     * Si dentro del objeto pasado como parámetro se actualiza key, y esta no 
     * existe, se crea un nuevo objeto.
     * @param {string} storeName Nombre del almacén de datos
     * @param {mixed} columnName Nombre de la columna por la que se busca, si es falso, se busca por la PK de la tabla
     * @param {mixed} value Valor que se quiere buscar en la columna
     * @param {object} object Objeto con las actualizaciones
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.update=function(storeName,columnName,value,object,callback){
        if(self.params.debugCrud)debug("upd() - Transaction started","info",self.params.debugLevel+2);
        
        
        callback(false);
        
        //Se obtiene el anterior valor de la base de datos
//        self.get(storeName,columnName,value,function(err,older){
//            if(err){
//                if(self.params.debugCrud)debug("upd() - Cannot get the previous value, inserting new object in DB","info",self.params.debugLevel+3);
//                self.add(storeName,object,function(err){
//                    if(callback)callback(err);
//                });
//            }else{
//                var tx = self.db.transaction([storeName],"readwrite");
//                var store = tx.objectStore(storeName);
//                
//                // Retorna la versión anterior del objeto y lo actualiza con el nuevo
////                var older = request.result;
//                var newer=$.extend(older,object);
//                
//                //Si no se para el nombre de la columna, se obtiene la PK de la lista de tablas
////                if(!columnName){
////                    var pk=getPkFromTable(storeName);
//////                    if(pk){
////                    columnName=getPkFromTable(storeName).name;
////                }
//                
////                console.debug(">>>>>>>>>>>>>> UPDATE <<<<<<<<<<<<<");
////                console.debug("store: "+storeName+" - column: "+columnName+" - value: "+value);
////                console.debug("***********************************");
////                console.debug(older);
////                console.debug("-----------------------------------");
////                console.debug(newer);
////                console.debug("***********************************");
//                var txIndex;
//                var requestUpdate;
//                if(columnName){
//                    txIndex=tx.index(columnName);
//                    requestUpdate = store.put(newer);
//                }else{
//                    requestUpdate = store.put(newer);
//                }
//                
//                // Vuelve a insertar el objeto en la base de datos
////                var requestUpdate = store.put(newer);
//                requestUpdate.onerror = function(e) {
//                    if(self.params.debugCrud)debug("upd() - Cannot update the object: "+columnName,"bad",self.params.debugLevel+2);
//                    if(callback)callback(e.target.error);
//                };
//                requestUpdate.onsuccess = function(e) {
//                    if(self.params.debugCrud)debug("upd() - Transaction ended","good",self.params.debugLevel+2);
//                    if(callback)callback(false,newer);
//                    //Agrega el objeto a la tabla de transacciones
//                    if(self.params.storeTransactions){
//                        self.get(storeName,key,function(err,row){
//                            if(!err){
//                                var transaction={
//                                    table:storeName,
//                                    key:key,
//                                    date:now(),
//                                    transaction:"UPDATE",
//                                    row:row
//                                };
//                                self.configurator.db.add("Transactions",transaction,function(err){
//                                    if(err){
//                                        if(self.params.debugCrud)debug("Add updated to transactions failed","bad",self.params.debugLevel+3);
//                                    }else{
//                                        if(self.params.debugCrud)debug("Add updated to transactions success","good",self.params.debugLevel+3);
//                                    }
//                                });
//                            }
//                        });
//                    }
//                };
//            }
//        });
        
        
        
        
        
        
        
        
        
        
        
        
        
        
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
        request.onerror = function(e){
            if(self.params.debugCrud)debug("del() - Cannot delete the object: "+key,"bad",self.params.debugLevel+2);
            if(callback)callback(e.target.error);
        };
        request.onsuccess = function(e) {
            if(self.params.debugCrud)debug("del() - Transaction ended","good",self.params.debugLevel+2);
            if(callback)callback(false);
            //Agrega el objeto a la tabla de transacciones
            if(self.params.storeTransactions){
                self.get(storeName,false,key,function(err,row){
                    if(!err){
                        var transaction={
                            table:storeName,
                            key:key,
                            date:now(),
                            transaction:"DELETE",
                            row:row
                        };
                        self.configurator.db.add("Transactions",transaction,function(err){
                            if(err){
                                if(self.params.debugCrud)debug("Add deleted to transactions failed","bad",self.params.debugLevel+3);
                            }else{
                                if(self.params.debugCrud)debug("Add deleted to transactions success","good",self.params.debugLevel+3);
                            }
                        });
                    }
                });
            }
        };
    };
    /**
    * Retorna un conjunto de objetos de la base de datos
    * @param {string} storeName Nombre del almacén de datos donde se quiere leer la información
    * @param {function} callback Función a la que se retornan los resultados
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
    /**************************************************************************/
    /******************************* DB METHODS *******************************/
    /**************************************************************************/
    /**
     * Retorna la lista de objectStores de la base de datos
     * @returns {array} Lista de nombres de objectStores
     */
    self.getStoreNames=function(){
        return self.db.objectStoreNames;
    };
    /**
     * Retorna un almacén de datos de la base de datos
     * @param {string} storeName Nombre del almacén de datos
     * @returns {store} almacén de objetos
     */
    self.getStore=function(storeName){
        var tx = self.db.transaction([storeName],"readwrite");
        var store = tx.objectStore(storeName);
        return store;
    };
    /**
     * Retorna la lista de columnas de un ObjectStore de la base de datos
     * @param {string} storeName Nombre del almacén de datos
     * @returns {array} Lista de índices del objectStore
     */
    self.getIndexNames=function(storeName){
        return self.getStore(storeName).indexNames;
    };
    /**
     * Retorna true si existe el Index en el ObjectStore
     * @param {string} storeName Nombre del almacén de datos
     * @param {string} indexName Nombre del índice que se quiere consultar
     * @returns {bool} True si existe el índice
     */
    self.indexExist=function(storeName,indexName){
        var exists=false;
        var indexes=self.getIndexNames(storeName);
        for(var i in indexes){
            if(indexes[i]===indexName){
                exists=true;
                break;
            }
        }
        return exists;
    };
    /**************************************************************************/
    /*************************** HTML5SYNC METHODS ****************************/
    /**************************************************************************/
    /**
     * Inicia el almacenamiento de las transacciones en la base de datos local html5sync
     */
    self.startStoreTransactions=function(){
        self.params.storeTransactions=true;
    };
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
        var columns=table.columns;
        var registers=new Array();
        for(var i in rows){
            var row=rows[i];
            var register=new Object();
            for(var j in row){
                var struct=structColumn(table.name,columns[j].name);
                if(struct){
                    if(struct.type==="int"){
                        register[columns[j].name]=parseInt(row[j]);
                    }else if(struct.type==="double"){
                        register[columns[j].name]=parseFloat(row[j]);
                    }else{
                        register[columns[j].name]=row[j];
                    }
                }else{
                    register[columns[j].name]=row[j];
                }
            }
            registers.push(register);
        }
        return registers;
    };
    /**
     * Retorna la estructura de una tabla. Debe ejecutarse luego de cargar correctamente
     * la base de datos
     * @param {string} tableName Nombre de la tabla
     * @return {object} Objeto con la estructura
     */
    function structTable(tableName){
        var output=false;
        for(var i in self.structTables){
            if(self.structTables[i].name===tableName){
                output=self.structTables[i];
            }
        }
        return output;
    };
    /**
     * Retorna los metadatos de una columna de una estructura de tabla. 
     * Debe ejecutarse luego de cargar correctamente la base de datos.
     * @param {string} tableName Nombre de la tabla
     * @param {string} columnName Nombre de la columna de la tabla
     * @return {object} Objeto con la estructura
     */
    function structColumn(tableName,columnName){
        var output=false;
        for(var i in self.structTables){
            if(self.structTables[i].name===tableName){
                var columns=self.structTables[i].columns;
                for(var j in columns){
                    if(columns[j].name===columnName){
                        output=columns[j];
                        break;
                    }
                }
            }
        }
        return output;
    };
    /**
     * Retorna el primer objeto columna PK de una tabla 
     * Debe ejecutarse luego de cargar correctamente la base de datos.
     * @param {string} tableName Nombre de la tabla
     * @return {object} Objeto de tipo columna
     */
    function getPkFromTable(tableName){
        var output=false;
        for(var i in self.structTables){
            if(self.structTables[i].name===tableName){
                var columns=self.structTables[i].columns;
                for(var j in columns){
                    if(columns[j].pk){
                        output=columns[j];
                        break;
                    }
                }
            }
        }
        return output;
    };
    /**
     * Procesa la lista de transacciones retornadas por la función sync.
     * @param {Transaction[]} transactions Lista de transacciones de sync
     * @param {function} callback Función para retornar los resultados
     * @todo Optimizar la insersión de transacciones para hacer una operación por tabla
     */
    self.processTransactions=function(transactions,callback){
        if(transactions.length>0){
            for(var i in transactions){
                var transaction=transactions[i];
                switch (transaction.type) {
                    case "INSERT":
                        self.add(transaction.tableName,transaction.row,function(err){
                            if(err){
                                if(callback)callback(err);
                            }
                        });
                        break;
                    case "UPDATE":
                        self.update(transaction.tableName,transaction.key,transaction.row,function(err){
                            if(err){
                                if(callback)callback(err);
                            }
                        });
                        break;
                    case "DELETE":
                        self.delete(transaction.tableName,transaction.key,function(err){
                            if(err){
                                if(callback)callback(err);
                            }
                        });
                        break;
                };
            }
        }
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
* @param {object} parameters Database parameters
* @param {function} callback Function to return the response
* @param {int} debugLevel Nivel de debug
*/
Database.loadDatabase=function(parameters,callback){
   if(!parameters.debugLevel)parameters.debugLevel=0;
   if(!parameters.storeTransactions)parameters.storeTransactions=false;
   var request = window.indexedDB.open(parameters.name);
   request.onerror = function(e) {
       callback(new Error("Unable to connect to the local database "+parameters.name));
   };
   request.onsuccess = function(e) {
        var dbParams={
            load:true,
            database:parameters.name,
            debugLevel:parameters.debugLevel,
            storeTransactions:parameters.storeTransactions
        };
        var database=new Database(dbParams,function(err){
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
        debug("This may take a while","wait",debugLevel);
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
    for(var i in table.columns){
        var column=table.columns[i];
        var unique=false;
        if(column.pk==true){
            unique=true;
            if(column.autoIncrement==true){
                key={keyPath:column.name,autoIncrement:true};
            }else{
                key={keyPath:column.name};
            }
        }
        var index={
            name:column.name,
            key:column.name,
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