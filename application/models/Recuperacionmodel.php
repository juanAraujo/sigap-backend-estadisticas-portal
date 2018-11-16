<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Recuperacionmodel extends CI_Model
{
	function __construct(){
		parent::__construct();
	}
	public function comprobar_existencia($email,$dni,$telefono){
		$query=$this->db->query
		("select ad.id_usuario from usuario u INNER JOIN administrativo ad ON u.id_usuario=ad.id_usuario 
		INNER JOIN usuario_perfil up ON up.id_usuario=u.id_usuario
		where ad.email = '".$email."' and ad.dni='".$dni."' and ad.telefono='".$telefono."' 
		and (up.id_perfil = 1 or up.id_perfil = 2) and up.estado_up=true;");
		//print_r($query);
		$data=$query->result_array();
		//echo "sdfsdf".count($data);
		if(count($data)>0){
			return $data['id_usuario'];
		} else {
			return false;
		}
			
	}
	public function actualizar_pass($id,$nuevapass){
		$this->db->query("update usuario set pass = '".$nuevapass."' where id_usuario= '".$id."'");
		if ($this->db->affected_rows() > 0) {   
			return TRUE; 
		} else {   
			return FALSE; 
		}
	}
}
?>