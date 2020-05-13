<?php
    class conexion
    {
        #conexion
        private $conexion;
        #controlador
        private $motordb;
        #servidor de datos
        private $servidor;
        #puerto de conexion;
        private $puerto;
        #usuario
        private $usuario;
        #clave
        private $clave;
        #nombre de db
        private $namedb;

        public function __construct()
        {
            $this->conexion = null;
            $this->motordb = "mysql";
            $this->servidor = "localhost";
            $this->puerto = 3306;
            $this->usuario = "tiendaOnline";
            $this->clave = "tiendaOnline2020";
            $this->namedb = "tiendaOnline";
        }

        /*
        en caso del controlador de base de datos
        no se mysql function para cambiar el controlador
        y otros parametros como clave, usuario y puerto
        */
        public function conexion_aux($motordb = 'mysql', $usuario, $clave, $nombredb, $servidor = 'localhost', $puerto = 3306)
        {
            $this->motordb = $motordb;
            $this->servidor = $servidor;
            $this->puerto = $puerto;
            $this->usuario = $usuario;
            $this->clave = $clave;
            $this->namedb = $nombredb;
        }

        /*
        funccion para realizar la conexion
        a la base de datos
        */
        public function conexion()
        {
            try{
                $optiones = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
                );
                $this->conexion = new PDO($this->motordb.':host='.$this->servidor.';port='.$this->puerto.';dbname='.$this->namedb.';charset=utf8',$this->usuario,$this->clave,$optiones);
            }catch(PDOException $e){
                FUNCIONES::logs_app(1,"DBconexion",$e->getMessage());
                die("Error de conexión: ".$e);
                return false;
            }
        }

        /*
        funccion para consultar datos
        parametro r para solo select 
        */
        public function consulta($sql, $cud = null)
        {
            try{
                $q = $this->conexion->prepare($sql);
                if($cud != null){
                    $q->setFetchMode(PDO::FETCH_ASSOC);
                    $q->execute();
                }
                return $q;
            }catch(PDOException $e){
                FUNCIONES::logs_app(1,'DBConsulta',$e);
                die("Error de consulta: ($sql) $e");
                return false;
            }
        }

        public function __destruct()
        {
            if($this->conexion){
                $this->conexion = null;
            }
        }
    }