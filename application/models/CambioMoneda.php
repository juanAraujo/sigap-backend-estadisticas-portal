<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class CambioMoneda extends CI_Model
{
    function __construct(){
		parent::__construct();
    }
    
    public function cambiarASoles($fecha)
    {
        do{
            $ch = curl_init("https://api.sunat.cloud/cambio/".$fecha);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $data = curl_exec($ch);
            curl_close($ch);

            //resto 1 día
            $fecha = date("Y-m-d",strtotime($fecha."- 1 days")); 
             
            echo $fecha;

        }while($info === '[]');

        

        $info = json_decode($data, true);
        $array_out = $data;
        echo ($array_out);

        if($data==='[]' || $info['fecha_inscripcion']==='--'){
            $datos = array(0 => 'nada');
            echo json_encode($datos);
        }else{
            $array_out = array(
            0 => $info['compra'], 
            1 => $info['venta'],
            );
            echo json_encode($datos);
        }
        echo ($array_out[0]);
        return $array_out;
    }

}
?>