html5sync
=========

Herramienta para sincronizar una base de datos del servidor con una en el cliente (HTML5).

Esta herramienta está en proceso de construcción, no hay ningún compromiso de que funcione.


Referencia
=========

html5sync pretende ser compatible con varios tipos de almacenamiento en el navegador web. Debido a que la base de datos más robusta en el momento para HTML5 es indexedDB, será la primera en ser implementada. Para más información, consulte los siguientes enlaces:

IndexedDB:
* Conceptos básicos: https://developer.mozilla.org/en-US/docs/IndexedDB/Basic_Concepts_Behind_IndexedDB
* Usando IndexedDB: https://developer.mozilla.org/en-US/docs/IndexedDB/Using_IndexedDB

indexedDB
=========

* ObjectStore
    Crea un almacén de objetos que se puede definir de la siguiente manera:
    |Key Path    Key Generator 	Description
    |(keyPath)   (autoIncrement)
    |__________________________________________________________________________________
     No          No              This object store can hold any kind of value, 
                                 even primitive values like numbers and strings. 
                                 You must supply a separate key argument whenever 
                                 you want to add a new value.
     Yes     	No                 This object store can only hold JavaScript objects. 
                                 The objects must have a property with the same name 
                                 as the key path.
     No          Yes             This object store can hold any kind of value. The 
                                 key is generated for you automatically, or you can 
                                 supply a separate key argument if you want to use a 
                                 specific key.
     Yes         Yes             This object store can only hold JavaScript objects. 
                                 Usually a key is generated and the value of the 
                                 generated key is stored in the object in a property 
                                 with the same name as the key path. However, if such 
                                 a property already exists, the value of that property 
                                 is used as key rather than generating a new key.


Changelog
=========

* v.0.0.2 - [2014-02-20]
    * Método para eliminar base de datos
    * Debug centralizado
    * Mostrar el número de versión en el debug
    * Método add de Database

* v.0.0.1 - [2014-02-18]
    * Ejemplo de bases de datos indexedDB
    * Uso de versiones de bases de datos
    * Creación de almacenes de objetos
    * Eliminación de almacenes de objetos
    * Documentación del código

* v.0.0.0 - [2014-02-18] - Exploración

Todo
=========
* Crear encabezados de archivos
* Poner licencia
* Manejo de errores
* Pruebas
