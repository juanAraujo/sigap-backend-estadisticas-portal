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
        if($data==='[]' || $info['fecha_inscripcion']==='--'){
            $datos = array(0 => 'nada');
            echo json_encode($datos);
        }else{
        $datos = array(
            0 => $info['ruc'], 
            1 => $info['razon_social'],
            2 => date("d/m/Y", strtotime($info['fecha_actividad'])),
            3 => $info['contribuyente_condicion'],
            4 => $info['contribuyente_tipo'],
            5 => $info['contribuyente_estado'],
            6 => date("d/m/Y", strtotime($info['fecha_inscripcion'])),
            7 => $info['domicilio_fiscal'],
            8 => date("d/m/Y", strtotime($info['emision_electronica']))
            );
            echo json_encode($datos);
        }
        return $array_out;
    }

}
?>
