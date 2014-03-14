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

Cliente
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

* Proceso de actualización de datos
    * 1. El navegador solicita la actualización
    * 2. El servidor retorna las tablas en JSON
    * 3. Verificar si cambió la versión de la base de datos
        * 3.1 Si cambió, actualiza la estructura de IndexedDB, pasa a 4
        * 3.2 No cambió, pasa a 4
    * 4. Para cada tabla -> Almacén de Objetos hacer
        * 4.1 Almacenar los datosde la tabla en el almacén

Servidor
=========
* Se debe permitir el acceso a html5sync a la base de datos. Para usar el ejemplo ver el script en: resources/database.sql
    * Configurar el usuario para que html5sync pueda acceder a la base de datos:
        mysql> GRANT ALL PRIVILEGES ON your_database.* TO 'html5sync'@'localhost' IDENTIFIED BY 'your_password';

* Se requiere la librería para SQLite
    * sudo apt-get install libsqlite3-0 libsqlite3-dev
    * sudo apt-get install php5-sqlite
    * sudo service apache2 restart

* Se requiere activar el caché para la aplicación en HTML5 (ver más http://diveintohtml5.info/offline.html)
    Para que la aplicación esté disponible fuera de línea, es necesario activar el uso del caché de la aplicación. Los pasos para que funcione son:
    * Indicar en el archivo .htaccess (en el caso de Apache) del servidor que se debe cargar el archivo en el formato MIME adecuado
        <code>AddType text/cache-manifest .manifest</code>
    * En cada página que requiera sincronización de la información incluir App Cache en la etiqueta <html>
        <code><html manifest="cache.manifest" type="text/cache-manifest"></code>
    * Crear un archivo .manifest para indicar qué debe estar displonible fuera de línea. En el ejemplo disponible con esta librería se incluye en la raíz del proyecto, en este caso debe ser similar a:
        <code>
            CACHE MANIFEST
            html5sync/client/core/Database.js
            html5sync/client/css/base.css
            html5sync/client/jquery/jquery-2.1.0.min.js
            NETWORK:
            *
            FALLBACK:
        </code>
    * Agregar en el archivo cache.manifest todos los recursos que se requieran fuera de línea

* Detección de cambios y soncronización de las tablas
    El sistena permite dos modos de detección de cambios en los datos de una tabla, que se establecen en la variable updateMode de config.php:
        - updatedColumn: Implica que html5sync debe tener permiso para crear
                         una columna adicional en cada tabla a sincronizar. 
                         Esta columna contiene la fecha de la última actualización
                         de cada registro. Además html5sync debe poder crear
                         un trigger en la base de datos para actualizar dicha
                         columna. Este método implica además que cuando se 
                         insertan registros en las tablas afectadas, se definan
                         de manera explícita las columnas:
                         INSERT INTO table_a (filed1,field2) VALUES (value1,value2)
                         para que se inserte automáticamente la fecha de actualización
        - hashUpdate:    Se usa una función hash para convertir el contenido
                         de la tabla en una cadena que se compara con un estado
                         anterior. Este procedimiento no es invasivo en la base
                         de datos, pero puede requerir mucho tiempo si se trata
                         de muchos registros. [en construcción]
    Además de sincronizar las tablas, también se requiere acctualizar la estructura de las tablas cuando hayan cambios. Para esto se usa una función de hash sobre la concatenación de la estructura de todas las tablas para un usuario, así, si alguna cambia, se actualiza toda la estructura.

* Proceso de actualización de datos
    * 1. El navegador solicita actualización
    * 2. El servidor carga la lista de tablas a sincronizar del archivo config.php
    * 3. Para cada tabla hacer
        * 3.1 Buscar en SQLite si existe un estado de esa tabla para el usuario
            * 3.1.1 Si existe, compara el último estado almacenado con el estado actual de la tabla
                * 3.1.1.1 Si cambió, aumenta en uno el número de la versión y pasa a 3.2
                * 3.1.1.2 No cambió, deja el mismo número de versión y pasa a 3.2
            * 3.1.2 No existe, inserta el primer estado de la tabla para el usuario, pasa a 3.2
        * 3.2 Almacena la tabla en el array de envío
        * 3.3 Carga los datos de la tabla
    * 4. Convierte las tablas y sus datos a JSON
    * 5. Envía la lista de tablas (en JSON) con sus versiones al navegador

Sync
=========
Estrategias de sincronización de la base de datos:
* Bloqueo: Se manejan dos estados para una tabla que dependen de las cuatro operaciones CRUD que el usuario vaya a realizar sobre ella.
    * Create: No requiere bloqueo
      Cada que sincronicen las bases de datos de cliente y servidor, se insertan los registros en orden de creación
    * Read: No requiere bloqueo
      Las consultas se hacen sobre la base de datos y no afectan a otros usuarios
    * Update: Requiere bloqueo
      Actualizar los datos de un registro puede modificar el estado de partida de otro usuario. Se requiere que cuando un usuario tome los datos, la tabla se bloquee y se libera cuando el usuario se vuelva a conectar.
    * Delete: Requiere bloqueo
      Eliminar un registro puede modificar el estado de partida de otro usuario. Se requiere que cuando un usuario tome los datos, la tabla se bloquee y se libera cuando el usuario se vuelva a conectar.   


Changelog
=========

* v.0.0.7 - [2014-03-11]
    * Creación del "updateMode" tipo "updatedColumn"
    * Creación automática de la columna de actualización
    * Creación de trigger para actualizar la columna
    * Creción de las clases Html5Sync y User
    * Se detecta si un usuario puede o no cargar una tabla por id de usuario o por rol
    * Detección de cambios en la estructura y en los datos en cada sync de ajax
    * Primera versiónd de la documentación (html5sync/docs/html/)
    * Acumulación de ajax pendientes para mostrara el cargador

* v.0.0.6 - [2014-03-08]
    * Optimización del sistema de comparación de estados por medio de Hash
    * Pruebas con grandes cantidades de datos
    * Uso de App Cache para cargar la aplicación cuando esté fuera de línea

* v.0.0.5 - [2014-03-06]
    * Creación de la clase de la base de datos de estado con PDO
    * Pruebas con SQLite para almacenar el estado de la librería
    * Clase para manejar el estado

* v.0.0.4 - [2014-03-03]
    * Creación de las clases de carga de tablas genéricas
    * Carga de tablas desde configuración
    * Parametrización de la librería
    * Conversión de tablas a JSON

* v.0.0.3 - [2014-02-23]
    * Creación de la clase Sync para sincronización
    * Verifica el estado de la conexión con el servidor
    * Primera versión de la capa de datos

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
* Verificar condiciones de fallo (tablas que no existan)
* Implemetar seguridad en la base de datos de estado SQLite
* Crear un paginador en PHP para manejar grandes cantidades de datos
* Limitar la carga de un script con AJAX cuando se ya se haya solicitado y no se haya terminado
* Parametrizar los tiempos de sincronización desde PHP
* Incluir en el README la documentación de config.php
* Eliminar la base de datos del navegador después de un tiempo si conexión
* Sistema de verificación de transmisión correcta de datos
* Bloquear librería mientras se recarga la estructura

* Verificar la creación de indexedDB antes de cargar la estructura


Licencia MIT
=========
The MIT License (MIT) Copyright (c) 2014

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.