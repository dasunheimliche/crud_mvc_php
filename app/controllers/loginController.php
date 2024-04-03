<?php

	namespace app\controllers;

	use app\models\ConectionError;
	use app\models\mainModel;

	class loginController extends mainModel{

		/*----------  Controlador iniciar sesion  ----------*/
		public function iniciarSesionControlador(){

			$usuario=$this->limpiarCadena($_POST['login_usuario']);
		    $clave=$this->limpiarCadena($_POST['login_clave']);

			if(empty($usuario) || empty($clave)) {
				$this->mostrarError('No has llenado todos los campos que son obligatorios');
				return; 
			}

			# Verificando usuario #

			try {
				$check_usuario=$this->ejecutarConsulta("SELECT * FROM usuario WHERE usuario_usuario='$usuario'");
				$check_usuario=$check_usuario->fetch();

				if(!$check_usuario || !password_verify($clave,$check_usuario['usuario_clave'])) {
					$this->mostrarError('Usuario o clave incorrectos');
					return;
				}

				$_SESSION['id']=$check_usuario['usuario_id'];
				$_SESSION['nombre']=$check_usuario['usuario_nombre'];
				$_SESSION['apellido']=$check_usuario['usuario_apellido'];
				$_SESSION['usuario']=$check_usuario['usuario_usuario'];
				$_SESSION['foto']=$check_usuario['usuario_foto'];

				if(headers_sent()){
					echo "<script> window.location.href='".APP_URL."dashboard/'; </script>";
				}else{
					header("Location: ".APP_URL."dashboard/");
				}

			} catch (ConectionError $e) {
				$this->mostrarError($e->getMessage());
				return; 
			} catch (\PDOException $p) {
				$this->mostrarError($p->getMessage());
				return; 
			}
		}
		/*----------  Controlador de errores  ----------*/

		private function mostrarError($mensaje) {

			echo"<script>
					Swal.fire({
						icon: 'error', 
						title: 'Ocurrió un error inesperado', 
						text: '{$mensaje}'
					});
				</script>";
    	}

		/*----------  Controlador cerrar sesion  ----------*/
		public function cerrarSesionControlador(){

			session_destroy();

		    if(headers_sent()){
                echo "<script> window.location.href='".APP_URL."login/'; </script>";
            }else{
                header("Location: ".APP_URL."login/");
            }
		}

	}