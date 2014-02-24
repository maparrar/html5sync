<?php
/** FileServer File
* @package core @subpackage  */
/**
* FileServer Class
* 
* La ruta de los archivos pasados debe ser del tipo:
*  "users/foo/bar/file.ext"
* Se buscará en el folder de datos de la aplicación. Esta ruta se llamará 
* "ruta abstracta" o "abstract path"
*
* @author https://github.com/maparrar/maqinato
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class FileServer{
    /**
     * Define si se debe acceder a los archivos de la aplicación en un folder o
     * una URL extarna
     * @var mixed:
     *      false: No carga archivos para la aplicación
     *      "local": Lee los datos de un folder dentro de la aplicación
     *      "external": Lee los datos de una fuente externa por medio de una URL
     *      "s3": Lee los datos de un bucket S3 de AWS
     */
    protected $source;
    /**
     * Define si se accede por SSL a los archivos externos
     * @var bool true para acceso seguro al servidor de archivos (debe estar 
     *           configurado en el servidor), false en otro caso
     */
    protected $isSSL;
    /**
     * Dominio del servidor de archivos para acceder a los datos, no incluye el 
     * protocolo, pues se define en la variable isSSL. No se usa en caso de 
     * source="local"
     * @var string Dominio en caso de source="external", por ejemplo:
     *      - "www.maqinato.com"
     *      - "s3.amazonaws.com"
     */
    protected $domain;
    /**
     * Bucket o contenedor, usado principalmente en servidores de datos externos
     * como AWS. No se usa en caso de source="local"
     * @var string Contenedor de archivos en caso de datos externos como AWS
     */
    protected $bucket;
    /**
     * Folder raíz de almacenamiento
     * @var string:
     *      - En caso de que source="local" debe ser una ruta relativa dentro
     *        del folder de la aplicación. P.e.
     *          - si la aplicación está en la ruta: "/var/www/maqinato" y el folder 
     *            de datos en "/var/www/maqinato/data/" se debe pasar a esta 
     *            variable el valor "data/"
     *          - si la aplicación está en la ruta: "/var/www/maqinato" y el folder 
     *            de datos en "/var/www/maqinato/foo/data" se debe pasar a esta 
     *            variable el valor "foo/data/"
     *      - En caso de source="external", debe ser el folder que contiene los
     *        datos. P.e.
     *          - si los datos están almacenados en "http://dataserver.com/foo/data"
     *            el valor de esta variable debe ser: "foo/data/"
     *      - En caso de source="s3"
     *          - si se trata de un proveedor de datos externos como AWS que requiere
     *            un bucket o contenedor, se especifica en otra variable, excluyendo
     *            en esta variable el nombre del bucket. Para un servidor
     *            "http://s3.amazonaws.com/bucket_name/data" el valor de esta
     *            variable debe ser: "data/".
     */
    protected $folder;
    /**
     * Clave de acceso al servidor de archivos, por ahora solo se usa con servidores
     * AWS.
     * @var string Clave de acceso al servidor de archivos
     */
    protected $accessKey;
    /**
     * Clave secreta para acceso al servidor de archivos. Solo usada para AWS.
     * @var string Clave secreta para aceeder al servidor de archivos
     */
    protected $secretKey;
    /**
     * Variable que almacena la clase para manejar el servidor en casos como el 
     * de AWS S3, donde la clase S3.php codifica la información para acceder datos
     * externos.
     * @var mixed False si no se requiere, de tipo S3  si es de AWS, otras requeridas
     *            en el futuro.
     */
    private $server;
    /**
    * Constructor
    * @param string $source Tipo de acceso al servidor de archivos        
    * @param bool $isSSL Si se debe usar SSL        
    * @param string $domain Dominio del servidor de archivos        
    * @param string $bucket Contenedor de archivos en el servidor        
    * @param string $folder Folder de los archivos        
    * @param string $accessKey Clave de acceso al servidor de archivos        
    * @param string $secretKey Clave secreta de acceso al servidor de archivos        
    */
    function __construct($source="",$isSSL=false,$domain="",$bucket="",$folder="",$accessKey=null,$secretKey=null){        
        $this->source=$source;
        $this->isSSL=$isSSL;
        $this->domain=$domain;
        $this->bucket=$bucket;
        $this->folder=$folder;
        $this->accessKey=$accessKey;
        $this->secretKey=$secretKey;
        $this->server=false;
        $this->configServer();
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter source
    * @param string $value Tipo de acceso al servidor de archivos
    * @return void
    */
    public function setSource($value) {
        $this->source=$value;
    }
    /**
    * Setter isSSL
    * @param bool $value Si se debe usar SSL
    * @return void
    */
    public function setIsSSL($value) {
        $this->isSSL=$value;
    }
    /**
    * Setter domain
    * @param string $value Dominio del servidor de archivos
    * @return void
    */
    public function setDomain($value) {
        $this->domain=$value;
    }
    /**
    * Setter bucket
    * @param string $value Contenedor de archivos en el servidor
    * @return void
    */
    public function setBucket($value) {
        $this->bucket=$value;
    }
    /**
    * Setter folder
    * @param string $value Folder de los archivos
    * @return void
    */
    public function setFolder($value) {
        $this->folder=$value;
    }
    /**
    * Setter accessKey
    * @param string $value Clave de acceso al servidor de archivos
    * @return void
    */
    public function setAccessKey($value) {
        $this->accessKey=$value;
    }
    /**
    * Setter secretKey
    * @param string $value Clave secreta de acceso al servidor de archivos
    * @return void
    */
    public function setSecretKey($value) {
        $this->secretKey=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: source
    * @return string
    */
    public function getSource() {
        return $this->source;
    }
    /**
    * Getter: isSSL
    * @return bool
    */
    public function getIsSSL() {
        return $this->isSSL;
    }
    /**
    * Getter: domain
    * @return string
    */
    public function getDomain() {
        return $this->domain;
    }
    /**
    * Getter: bucket
    * @return string
    */
    public function getBucket() {
        return $this->bucket;
    }
    /**
    * Getter: folder
    * @return string
    */
    public function getFolder() {
        return $this->folder;
    }
    /**
    * Getter: accessKey
    * @return string
    */
    public function getAccessKey() {
        return $this->accessKey;
    }
    /**
    * Getter: secretKey
    * @return string
    */
    public function getSecretKey() {
        return $this->secretKey;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
     * Configura el servidor de acuerdo a los datos ingresados
     */
    private function configServer(){
        if($this->source==="local"){
            
        }elseif($this->source==="external"){
            
        }elseif($this->source==="s3"){
            $this->server=new S3($this->accessKey,$this->secretKey,$this->isSSL,$this->domain);
        }
        
    }
    /** 
     * Retorna la ruta real de un archivo a partir de la ruta abstracta o relativa 
     * al folder de datos
     * @param string $abstractPath Ruta abstracta o relativa al folder de datos, i.e.: 
     *      - "users/richard.png"
     *      - "users/images/10.png"
     * @return mixed Retorna la ruta real de acuerdo al source elegido
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png?AWSAccessKeyId=AKIAJP2UTAR7UQEPY72Q&Expires=1356955251&Signature=WfmXKKXhBzjIU1ZsM4UO7F%2Bo3QM%3D"
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png"
     *      - "foo/data/combinations/110.png"
     */
    public function dataUrl($abstractPath){
        $path=false;
        if($this->source==="local"){
            $path=$this->folder.$abstractPath;
        }elseif($this->source==="external"){
            if($this->isSSL){
                $path="https://";
            }else{
                $path="http://";
            }
            $path.=$this->domain."/".$this->folder.$abstractPath;
        }elseif($this->source==="s3"){
            $path=$this->server->getAuthenticatedURL($this->bucket,$this->folder.$abstractPath,10000,false,$this->isSSL);
        }
        return $path;
    }
    /**
     * Alias de dataUrl para ser usado como versión corta en la carga de imágenes
     * @param string $abstractPath Ruta abstracta de la imagen
     *      - "folder/110.png"
     * @return string Retorna la ruta de la imagen:
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png?AWSAccessKeyId=AKIAJP2UTAR7UQEPY72Q&Expires=1356955251&Signature=WfmXKKXhBzjIU1ZsM4UO7F%2Bo3QM%3D"
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png"
     *      - "/home/operator/foo/data/combinations/110.png"
     */
    public function img($abstractPath){
        return $this->dataUrl($abstractPath);
    }
    /** 
     * Save a file in the data folder, selected from filesystem or rest
     * @param string $source una ruta estándar donde está el archivo a guardar 
     *      - "/tmp/tempfile123.png"
     * @param string $abstractDestination Nombre de la ruta abstracta donde se guardará
     *      - "folder/saved.png"
     * @return bool:
     *      true if could save the file
     *      false otherwise
     */
    public function dataPut($source,$abstractDestination){
        $success=false;
        if($this->source==="local"){
            if(copy($source,$this->folder.$abstractDestination)){
                $success=true;
            }
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            if ($this->server->putObjectFile($source,$this->bucket,$this->folder.$abstractDestination,S3::ACL_AUTHENTICATED_READ)){
                $success=true;
            }
        }
        return $success;
    }
    /** 
     * Alias para dataPut, sube un archivo al sistema de archivos
     * @param string $source una ruta estándar donde está el archivo a guardar 
     *      - "/tmp/tempfile123.png"
     * @param string $abstractDestination Nombre de la ruta abstracta donde se guardará
     *      - "folder/saved.png"
     * @return bool:
     *      true if could save the file
     *      false otherwise
     */
    public function saveFile($source,$abstractDestination){
        return self::dataPut($source,$abstractDestination);
    }
    /** 
     * Función utilizada para guardar una imagen que ha sido subida por medio de
     * un FILE input.
     * @param string $source Path and filename, i.e.: 
     *      - $_FILES["file"]["tmp_name"]
     * @param string $abstractDestination Name to save the file in the data folder:
     *      - "folder/110.png"
     * @return bool:
     *      true if could save the file
     *      false otherwise
     */
    public function saveUploadedFile($source,$abstractDestination){
        $success=false;
        if($this->source==="local"){
            if(move_uploaded_file($source,$this->folder.$abstractDestination)){
                $success=true;
            }
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            if(self::dataPut($source,$abstractDestination)){
                $success=true;
            }
        }
        return $success;
    }
    /** 
     * Copia un archivo de una ruta abstracta a otra ruta abstracta
     * @param string $abstractSource Ruta abstracta del archivo de origen
     *      - "foo/110.png"
     *      - "bar/images/10.png"
     * @param string $abstractDestination Ruta abstracta del archivo destino
     *      - "bar/110.png"
     *      - "users/foo/10.png"
     * @return bool True if successful
     */
    public function copyFile($abstractSource,$abstractDestination){
        $success=false;
        if($this->source==="local"){
            $success=copy($this->folder.$abstractSource,$this->folder.$abstractDestination);
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            $success=$this->server->copyObject($this->bucket,$this->folder.$abstractSource,$this->bucket,$this->folder.$abstractDestination);
        }
        return $success;
    }
    /** 
     * Elimina un archivo del folder de datos a partir de su ruta abstracta
     * @param string $abstractPath Ruta abstracta del archivo que se eliminará 
     *      - "foo/110.png"
     *      - "users/bar/10.png"
     * @return bool True if successful
     */
    public function deleteFile($abstractPath){
        $object=false;
        if($this->source==="local"){
            unlink($this->folder.$abstractPath);
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            $object=$this->server->deleteObject($this->bucket,$this->folder.$abstractPath);
        }
        return $object;
    }
    /**
     * Verifica si un archivo existe, Si es externo, verifica la URL, sino verifica
     * con file_exist()
     * @abstract Debido a la demora en la comprobación si una imagen existe o no
     * se verifica si la imagen carga directamente en la etiqueta img y
     * se crea el directorio para imágenes por default que va con el
     * código de la aplicación. Esta función no se usa para archivos externos
     * en self::img() por ahora.
     * @param string $abstractPath Ruta abstracta del archivo a comprobar 
     */
    public function fileExist($abstractPath){
        $exist=false;
        if($this->source==="local"){
            if(file_exists($this->dataUrl($abstractPath))){
                $exist=true;
            }
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            //TODO: Verificar archivos remotos es costoso computacionalmente
        }
        return $exist;
    }
    /** 
     * Retorna un archivo del folder de datos
     * @param string $abstractPath Ruta abstracta del archivo 
     *      - "foo/110.png"
     *      - "users/bar/10.png"
     * @return mixed Retorna el objeto de la ruta abstracta
     */
    public function getFile($abstractPath){
        if($this->source==="local"){
            unlink($this->folder.$abstractPath);
        }elseif($this->source==="external"){
            //TODO: Por implementar, posiblemente con SFTP
        }elseif($this->source==="s3"){
            $object=$this->server->getObject($this->bucket,$this->folder.$abstractPath);
        }
        return $object;
    }
}