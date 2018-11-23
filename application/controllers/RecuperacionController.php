<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class RecuperacionController extends REST_Controller{
	function __construct(){
        parent::__construct();
        $this->load->model('recuperacionmodel');
    }
    public function index_post(){
    	$nombre_usuario=$this->post("nombre_usuario");
    	$email=$this->post("email");
    	$dni=$this->post("dni");
    	$telefono=$this->post("telefono");
    	$contrasena=$this->post("pass");
    	if($nombre_usuario!=null && $email!=null && $dni!=null && $telefono!=null && $contrasena!=null){
    		$id=$this->recuperacionmodel->comprobar_existencia($nombre_usuario,$email,$dni,$telefono);
    		if($id!=false){
    			$respuesta=$this->recuperacionmodel->actualizar_pass($id,$contrasena);
                if($respuesta==true){
                    $array_out = array("result"=>"cambiada");
                }
                else {
                    $array_out = array("result"=>"error");
                }
			}
			else{
				$array_out = array("result"=>"no existe");
			}
		}
		else{
			$array_out = array("return"=>"failure");
		}
    	$this->response($array_out);
    }
}
?>
