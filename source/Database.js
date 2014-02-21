/*
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
 *  @param {function} callback Función que garantiza que en su contexto ya se ha cargado la base de datos
 */
var Database = function(params,callback) {
    
//    console.debug(callback);
    
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.version = 8;       //Versión de la Base de datos indexedDB
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
        options: {
            overwriteObjectStores: true
        }
    };
    self.params = $.extend(def, params);
    /*
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Database = function() {
        if(window.indexedDB !== undefined) {
            self.request = window.indexedDB.open(self.params.database, self.version);
            debug("Iniciando acceso a la base de datos: "+self.params.database+" - versi&oacute;n: "+self.version);
            //Asigna los eventos
            events();
        }else{
            debug("Este navegador no soporta indexedDB");
        }
    }();
    
    /**************************************************************************/
    /**************************** PRIVATE METHODS *****************************/
    /**************************************************************************/
    /*
     * Método privado que asigna funciones a los eventos
     */
    function events() {
        /*
         * Evento del request para manejar los errores. Se dispara si
         * por ejemplo, un usuario no permite que se usen bases de
         * datos indexedDB en el navegador.
         */
        self.request.onerror = function(e) {
            debug("No es posible conectar con la base de datos local");
        };
        /*
         * Evento del request cuando es posible usar la base de datos
         * indexedDB en el navegador.
         */
        self.request.onsuccess = function(e) {
            self.db = self.request.result;
            self.callback();
            debug("Se he accedido con &eacute;xito la base de datos: "+self.params.database);
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
                debug("... Se ha creado el almac&eacute;n de datos: " + storeParams.name);
                //Se crea el conjunto de índices para cada almacén
                for (var j in storeParams.indexes){
                    var indexParams=storeParams.indexes[j];
                    var index = store.createIndex(indexParams.name,indexParams.key,indexParams.params);
                    
                    console.debug(index);
                    
                    debug("... ... Se ha creado el &iacute;ndice: " + indexParams.name);
                }
            }
        };
    };
    /*
     * Borra un almacén de objetos. ¡¡¡ Elimina todo el contenido !!! 
     * @param {string} name Nombre del almacén de objetos
     */
    function deleteStore(name){
        try{
            self.db.deleteObjectStore(name);
            debug("... Se elimin&oacute; con &eacute;xito el almac&eacute;n: "+name);
        }catch(e){
            debug("... No hay versi&oacute;n anterior del almac&eacute;n: "+name);
        }
    };
    
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /*
     * Inserta objetos en un almacén. Recibe un objeto o un array de objetos
     * @param {string} store Nombre del almacén de datos donde se quiere insertar la información
     * @param {object[]/object} data Objeto o array de objetos
     */
    self.add=function(storeName,data){
        debug("Iniciando transacci&oacute;n: add");
        var tx = self.db.transaction([storeName], "readwrite");
        var store = tx.objectStore(storeName);        
        //Evento que se dispara cuando se finaliza la transacción con éxito
        tx.oncomplete = function(e) {
            debug("... Transacci&oacute;n finalizada: add");
        };
        //Evento que maneja los errores en la transacción
        tx.onerror = function(e) {
            debug("... Error en la transacci&oacute;n: add"+JSON.stringify(e));
        };
        
        if(Object.prototype.toString.call(data)==="[object Array]"){
            for (var i in data) {
                var request = store.add(data[i]);
                debug("... ... Agregando: "+JSON.stringify(data[i]));
                request.onerror = function(e){
                    debug("... ... Error agregando: "+JSON.stringify(data[i])+" .::. "+request.error);
                };
            }
        }else{
            var request = store.add(data);
            debug("... ... Agregando: "+JSON.stringify(data));
            request.onerror = function(e){
                debug("... ... Error agregando: "+JSON.stringify(data)+" .::. "+request.error);
            };
        }
    };
    /*
     * Borra una base de datos indexedDB ¡¡¡ Elimina todo el contenido !!! 
     * @param {string} name Nombre de la base de datos
     */
    self.deleteDatabase=function (name){
        var request=window.indexedDB.deleteDatabase(name);
        request.onsuccess = function () {
            debug("Base de datos eliminada: "+name);
        };
        request.onerror = function () {
            debug("No se puede borrar la base de datos: "+name);
        };
    };
};