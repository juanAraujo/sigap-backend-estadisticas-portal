<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class CambioMoneda extends CI_Model
{
    function __construct(){
		parent::__construct();
    }
    
    public function cambiarASoles($fecha)
    {
        $ch = curl_init("https://api.sunat.cloud/cambio/".$fecha);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        $info = json_decode($data, true);
        $array_out = $data;
        echo ($array_out);
        echo ($info);

        if($data==='[]' || $info['fecha_inscripcion']==='--'){
            $datos = array(0 => 'nada');
            echo json_encode($datos);
        }else{
            $array_out = array();
            if(count($data)>0){
                foreach ($data as $registro) {
                    $array_out[] = $registro;
                }
            }
        }
        echo ($array_out);
        return $array_out;
    }

}
?>
