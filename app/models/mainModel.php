<?php
	
	namespace app\models;

	use \PDO;
	use Exception;
	use PDOException;


	$serverConfigPath = __DIR__."/../../config/server.php";

	try {

		if (file_exists($serverConfigPath)) {
			require_once $serverConfigPath;
		} else {
			throw new Exception("El archivo de configuración no existe: " . $serverConfigPath);			
		}
		
	} catch (Exception $e) {
		echo "Error: " . $e->getMessage();
	}

	class ConectionError extends Exception{}

	class mainModel{

		private $server=DB_SERVER;
		private $db=DB_NAME;
		private $user=DB_USER;
		private $pass=DB_PASS;
		private $conn;

		public function __construct() {
			$this->conectar();
		}


		/*----------  Funcion conectar a BD  ----------*/
		protected function conectar(){
			try {
				$dns = "mysql:host=$this->server;dbname=$this->db;charset=utf8mb4";
				$this->conn = new PDO($dns, $this->user, $this->pass);

			} catch (PDOException $e) {
				echo '<script>console.error("Error al conectar a la base de datos: '.$e->getMessage().'")</script>';
			}
		}

		/*----------  Funcion ejecutar consultas  ----------*/
		protected function ejecutarConsulta($consulta){
				if(!$this->conn) {
					throw new ConectionError("Error al conectar a la base de datos, inténtelo de nuevo en unos minutos");
				}

				$sql=$this->conn->prepare($consulta);
				$sql->execute();
				return $sql;
		}


		/*----------  Funcion limpiar cadenas  ----------*/
		public function limpiarCadena($cadena){

			$palabras=["<script>","</script>","<script src","<script type=","SELECT * FROM","SELECT "," SELECT ","DELETE FROM","INSERT INTO","DROP TABLE","DROP DATABASE","TRUNCATE TABLE","SHOW TABLES","SHOW DATABASES","<?php","?>","--","^","<",">","==","=",";","::"];

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			foreach($palabras as $palabra){
				$cadena=str_ireplace($palabra, "", $cadena);
			}

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			return $cadena;
		}


		/*---------- Funcion verificar datos (expresion regular) ----------*/
		protected function verificarDatos($filtro,$cadena){
			if(preg_match("/^".$filtro."$/", $cadena)){
				return false;
            }else{
                return true;
            }
		}


		/*----------  Funcion para ejecutar una consulta INSERT preparada  ----------*/
		protected function guardarDatos($tabla,$datos){

			try {

				if(!$this->conn) {
					throw new Exception("Error al conectar a la base de datos, inténtelo de nuevo en unos minutos");
				}

				$query="INSERT INTO $tabla (";

				$C=0;
				foreach ($datos as $clave){
					if($C>=1){ $query.=","; }
					$query.=$clave["campo_nombre"];
					$C++;
				}
				
				$query.=") VALUES(";

				$C=0;
				foreach ($datos as $clave){
					if($C>=1){ $query.=","; }
					$query.=$clave["campo_marcador"];
					$C++;
				}

				$query.=")";
				$sql=$this->conn->prepare($query);

				foreach ($datos as $clave){
					$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
				}

				$sql->execute();

				return $sql;
			} catch (Exception $e) {
				echo "
				<script>
					Swal.fire({
						icon: 'error',
						title: 'Ocurrió un error inesperado',
						text: '" . $e->getMessage() . "'
					});
				</script>
				";
			}

			
		}


		/*---------- Funcion seleccionar datos ----------*/
        public function seleccionarDatos($tipo,$tabla,$campo,$id){

			if(!$this->conn) {
				throw new Exception("Error al conectar a la base de datos, inténtelo de nuevo en unos minutos");
			}

			$tipo=$this->limpiarCadena($tipo);
			$tabla=$this->limpiarCadena($tabla);
			$campo=$this->limpiarCadena($campo);
			$id=$this->limpiarCadena($id);

			if($tipo=="Unico"){
				$sql=$this->conn->prepare("SELECT * FROM $tabla WHERE $campo=:ID");
				$sql->bindParam(":ID",$id);
			}elseif($tipo=="Normal"){
				$sql=$this->conn->prepare("SELECT $campo FROM $tabla");
			}
			$sql->execute();

			return $sql;
			
		}


		/*----------  Funcion para ejecutar una consulta UPDATE preparada  ----------*/
		protected function actualizarDatos($tabla,$datos,$condicion){

			try {

				if(!$this->conn) {
					throw new Exception("Error al conectar a la base de datos, inténtelo de nuevo en unos minutos");
				}

				$query="UPDATE $tabla SET ";

				$C=0;
				foreach ($datos as $clave){
					if($C>=1){ $query.=","; }
					$query.=$clave["campo_nombre"]."=".$clave["campo_marcador"];
					$C++;
				}

				$query.=" WHERE ".$condicion["condicion_campo"]."=".$condicion["condicion_marcador"];

				$sql=$this->conn->prepare($query);

				foreach ($datos as $clave){
					$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
				}

				$sql->bindParam($condicion["condicion_marcador"],$condicion["condicion_valor"]);

				$sql->execute();

				return $sql;
			} catch (Exception $e) {
				echo "
				<script>
					Swal.fire({
						icon: 'error',
						title: 'Ocurrió un error inesperado',
						text: '" . $e->getMessage() . "'
					});
				</script>
				";
			}
			
			
		}


		/*---------- Funcion eliminar registro ----------*/
        protected function eliminarRegistro($tabla,$campo,$id){

			try {

				if(!$this->conn) {
					throw new Exception("Error al conectar a la base de datos, inténtelo de nuevo en unos minutos");
				}

				$sql=$this->conn->prepare("DELETE FROM $tabla WHERE $campo=:id");
				$sql->bindParam(":id",$id);
				$sql->execute();
				
				return $sql;
			} catch (Exception $e) {
				echo "
				<script>
					Swal.fire({
						icon: 'error',
						title: 'Ocurrió un error inesperado',
						text: '" . $e->getMessage() . "'
					});
				</script>
				";
			}

        }


		/*---------- Paginador de tablas ----------*/
		protected function paginadorTablas($pagina,$numeroPaginas,$url,$botones){
	        $tabla='<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

	        if($pagina<=1){
	            $tabla.='
	            <a class="pagination-previous is-disabled" disabled >Anterior</a>
	            <ul class="pagination-list">
	            ';
	        }else{
	            $tabla.='
	            <a class="pagination-previous" href="'.$url.($pagina-1).'/">Anterior</a>
	            <ul class="pagination-list">
	                <li><a class="pagination-link" href="'.$url.'1/">1</a></li>
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	            ';
	        }


	        $ci=0;
	        for($i=$pagina; $i<=$numeroPaginas; $i++){

	            if($ci>=$botones){
	                break;
	            }

	            if($pagina==$i){
	                $tabla.='<li><a class="pagination-link is-current" href="'.$url.$i.'/">'.$i.'</a></li>';
	            }else{
	                $tabla.='<li><a class="pagination-link" href="'.$url.$i.'/">'.$i.'</a></li>';
	            }

	            $ci++;
	        }


	        if($pagina==$numeroPaginas){
	            $tabla.='
	            </ul>
	            <a class="pagination-next is-disabled" disabled >Siguiente</a>
	            ';
	        }else{
	            $tabla.='
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	                <li><a class="pagination-link" href="'.$url.$numeroPaginas.'/">'.$numeroPaginas.'</a></li>
	            </ul>
	            <a class="pagination-next" href="'.$url.($pagina+1).'/">Siguiente</a>
	            ';
	        }

	        $tabla.='</nav>';
	        return $tabla;
	    }

		public function __destruct() {
			$this->conn = null;
		}
	    
	}