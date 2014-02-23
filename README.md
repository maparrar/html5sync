html5sync
=========

Herramienta para sincronizar una base de datos del servidor con una en el cliente (HTML5).

Esta herramienta está en proceso de construcción.

- maparrar: http://maparrar.github.io
- jomejia: https://github.com/jomejia


Referencia
=========

html5sync pretende ser compatible con varios tipos de almacenamiento en el navegador web. Debido a que la base de datos más robusta en el momento para HTML5 es indexedDB, será la primera en ser implementada. Para más información, consulte los siguientes enlaces:

IndexedDB:
* Conceptos básicos: https://developer.mozilla.org/en-US/docs/IndexedDB/Basic_Concepts_Behind_IndexedDB
* Usando IndexedDB: https://developer.mozilla.org/en-US/docs/IndexedDB/Using_IndexedDB

indexedDB
=========

* ObjectStore

    Crea un almacén de objetos que se puede definir de la siguiente manera (tomado de: https://developer.mozilla.org/en-US/docs/IndexedDB/Using_IndexedDB#Structuring_the_database):
    * Key Path (keyPath): NO - Key Generator (autoIncrement): NO
        This object store can hold any kind of value, even primitive values like numbers and strings. You must supply a separate key argument whenever you want to add a new value.
    * Key Path (keyPath): YES - Key Generator (autoIncrement): NO
        This object store can only hold JavaScript objects. The objects must have a property with the same name as the key path.
    * Key Path (keyPath): NO - Key Generator (autoIncrement): YES
        This object store can hold any kind of value. The key is generated for you automatically, or you can supply a separate key argument if you want to use a specific key.
    * Key Path (keyPath): YES - Key Generator (autoIncrement): YES
        This object store can only hold JavaScript objects. Usually a key is generated and the value of the generated key is stored in the object in a property with the same name as the key path. However, if such a property already exists, the value of that property is used as key rather than generating a new key.

Changelog
=========

* v.0.0.2 - [2014-02-22]
    * Método para eliminar base de datos
    * Debug centralizado
    * Mostrar el número de versión en el debug
    * Método add de Database
    * Método delete de Database
    * Método get de Database
    * Método update de Database
    * Manejo de errores en funciones asíncronas
    * Estándarización en el CRUD de Database

* v.0.0.1 - [2014-02-18]
    * Ejemplo de bases de datos indexedDB
    * Uso de versiones de bases de datos
    * Creación de almacenes de objetos
    * Eliminación de almacenes de objetos
    * Documentación del código

* v.0.0.0 - [2014-02-18] - Exploración

Todo
=========
* Pruebas
* Indicador de "procesando"


Licencia MIT
=========
The MIT License (MIT) Copyright (c) 2013

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.