/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para manejar las conexiones con el servidor y la transferencia de información
 * @param {object} params Objeto con los parámetros de la clase
 * @param {function} callback Función a la que se retornan los resultados
 */
var Connector = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    
    self.busyFunctions=new Array();  //Lista de funciones ocupadas que solo se pueden usar una vez al tiempo
    
    
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        ajaxFolder:"server/ajax/",
        showLoading: false          //Function passed like parameter that shows the Loading gift.
    };
    self.params = $.extend(def, params);
    self.showLoading=self.params.showLoading;
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Connector = function() {
        
    }();
    /**************************************************************************/
    /****************************** SETUP METHODS *****************************/
    /**************************************************************************/
    /**
     * Carga la configuración desde el servidor o desde la base de datos del 
     * navegador: BrowserDB.
     * Si no puede cargar desde el servidor, la carga de la base de datos, sino
     * retorna error.
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.loadConfiguration=function(callback,debugLevel){
        self.showLoading(true);
        if(!debugLevel)debugLevel=0;
        debug("Loading configuration from server...","info",debugLevel+1);
        $.ajax({
            url: self.params.ajaxFolder+"loadConfiguration.php",
            type: "POST"
        }).done(function(response){
            try{
                var data=JSON.parse(response);
                if(data.database===undefined||data.database===""){
                    returnErrors();
                }else{
                    debug("Configuration data loaded from server","good",debugLevel+1);
                    callback(false,data);
                }
            }catch(err){
                returnErrors();
            }
            self.showLoading(false);
        }).fail(function(){
            returnErrors();
            self.showLoading(false);
        });
        function returnErrors(){
            debug("Load configuration from server failed","bad",debugLevel+1);
            callback(new Error("Load configuration from server failed"));
        }
    };
    /**************************************************************************/
    /******************************* SYNC METHODS *****************************/
    /**************************************************************************/
    /**
     * Recarga la base de datos del navegador (BrowserDB) y la reemplaza. Luego de
     * usar esta función es necesario recargar todos los datos antes de usar la 
     * información
     * @param {string} databaseName Nombre de la database BrowserDB
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.reloadDatabase=function(databaseName,callback,debugLevel){
        if(!debugLevel)debugLevel=0;
        self.showLoading(true);
        if(!self.isBusy("reloadDatabase")){
            self.setToBusy("reloadDatabase");
            debug("Reloading database from server...","info",debugLevel);
            debug("THIS MAY TAKE A WHILE","wait",debugLevel);
            $.ajax({
                url: self.params.ajaxFolder+"reloadDatabase.php",
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                if(data.error){
                    if(callback)callback(new Error(data.error));
                }else{
                    //Configura los parámetros para crear la base de datos
                    var parameters={
                        database: databaseName,
                        version: parseInt(data.version),                //Versión de la base de datos
                        stores:Database.tablesToStores(data.tables),
                        debugLevel:debugLevel+1,
                        storeTransactions:true
                    };
                    var database=new Database(parameters,function(err){
                        if(err){
                            debug("Cannot create the database "+databaseName,"bad",debugLevel);
                            if(callback)callback(err);
                        }else{
                            debug("Database "+databaseName+" created","good",debugLevel);
                            //Si todo está bien, retorna la base de datos creada
                            if(callback)callback(false,database,data.tables);
                        }
                    });
                }
                self.setToIdle("reloadDatabase");
                self.showLoading(false);
            }).fail(function(){
                debug("Cannot reload database from server","bad",debugLevel);
                callback(new Error("Cannot reload database from server"));
                self.setToIdle("reloadDatabase");
                self.showLoading(false);
            });
        }
    };
    
    /**
     * Verifica si se cargaron todos los datos de una tabla, si faltan, carga la
     * siguiente página
     * @param {object} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.reloadTable=function(table,callback,debugLevel){
        if(!debugLevel)debugLevel=0;
        self.showLoading(true);
        $.ajax({
            url: self.params.ajaxFolder+"reloadTable.php",
            data:{
                tableName:table.name,
                initialRow:parseInt(table.initialRow)
            },
            type: "POST"
        }).done(function(response) {
            var data=JSON.parse(response);
            if(data.error){
                debug("SERVER: "+data.error,"bad",debugLevel+1);
                if(callback)callback(new Error("SERVER: "+data.error));
            }else{
                var table=data.table;
                var initialRow=parseInt(table.initialRow);
                var numberOfRows=parseInt(table.numberOfRows);
                var totalOfRows=parseInt(table.totalOfRows);
                debug("Loading "+table.name+": loaded "+(initialRow+numberOfRows)+" of "+totalOfRows,"good",debugLevel+3);
                //Si es la última tabla cargada, retorna la variable finished=true
                if((totalOfRows-(initialRow+1))<=numberOfRows){
                    debug("Table "+table.name+" loaded","good",debugLevel+2);
                    if(callback)callback(false,table,true);
                }else{
                    //Se usa un callback en el callback para esperar que los datos
                    //sean ingresados en BrowserDB antes de solicitar la siguiente página
                    if(callback)callback(false,table,false,function(err){
                        if(!err){
                            //Solicita la siguiente página de la tabla
                            table.initialRow=initialRow+numberOfRows;
                            self.reloadTable(table,callback,debugLevel);
                        }
                    });
                }
            }
            self.showLoading(false);
        }).fail(function(){
            if(callback)callback(new Error("Unable to reload data from the server"));
            self.showLoading(false);
        });
    };
    /**
     * Verifica si la conexión con el servidor está activa y actualiza el 
     * indicador de estado. Verifica si hay cambios en las tablas para el usuario,
     * si los hay, retorna los cambios.
     * @param {function} callback Función para retornar los resultados
     */
    self.sync=function(callback){
        if(!self.isBusy("sync")){
            debug("Synchronizing...","info",1);
            $.ajax({
                url: self.params.ajaxFolder+"sync.php",
                type: "POST"
            }).done(function(response) {
                var data=false;
                try{
                    data=JSON.parse(response);
                    if(data.error){
                        debug("SERVER: "+data.error,"bad",debugLevel+1);
                        if(callback)callback(new Error("SERVER: "+data.error));
                    }else{
                        //Retorna las transacciones para ser procesadas
                        if(callback)callback(false,data.transactions);
                    }
                }catch(e){
                    if(callback)callback(new Error("Error parsing server data"));
                }
            }).fail(function(){
                if(callback)callback(new Error("Could not sync"));
            });
        }
    };
    
    /**************************************************************************/
    /***************************** STATUS METHODS *****************************/
    /**************************************************************************/
    /**
     * Verifica si una función que no admite repeticiones está ocupada
     * @param {string} name Nombre de la función
     * @returns {bool} True si la función está ocupada, false en otro caso
     */
    self.isBusy=function(name){
        var busy=false;
        for(var i in self.busyFunctions){
            if(self.busyFunctions[i]===name){
                busy=true;
            }
        }
        return busy;
    };
    /**
     * Agrega el nombre de una función al array de funciones ocupadas que no se
     * pueden usar hasta que no se desocupen
     * @param {string} name Nombre de la función
     */
    self.setToBusy=function(name){
        if(!self.isBusy(name)){
            self.busyFunctions.push(name);
        }
    };
    /**
     * Elimina el nombre de una función del array de funciones ocupadas que no se
     * pueden usar hasta que no se desocupen.
     * @param {string} name Nombre de la función
     */
    self.setToIdle=function(name){
        for(var i in self.busyFunctions){
            if(self.busyFunctions[i]===name){
                self.busyFunctions.splice(i,1);
            }
        }
    };
};