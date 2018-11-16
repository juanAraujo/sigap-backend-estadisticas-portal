<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class RecuperacionController extends REST_Controller{
	function __construct(){
        parent::__construct();
        $this->load->model('recuperacionmodel');
    }
    public function index_get(){
    	//$tipo=$this->get("tipo");
    	$email=$this->get("email");
    	$dni=$this->get("dni");
    	$telefono=$this->get("telefono");
    	$contrasena=$this->get("pass");
    	$array_out = array();
    	if($email!=null && $dni!=null && $telefono!=null && $contrasena!=null){
    		$id=$this->emailmodel->comprobar_existencia($email);
    		if($id!=false){
    			$respuesta=$this->recuperacionmodel->actualizar_pass($id,$contrasena);
                if($respuesta==true){
                    $array_out = array("result"=>"success");
                }
                else {
                    $array_out = array("result"=>"error");
                }
    		}
    	}
    	$this->response($array_out);
    }
}
?>