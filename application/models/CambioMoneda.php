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
            $info = json_decode($data, true);

        }while($data === '[]');

        //resto 1 día
        $fecha = date("Y-m-d",strtotime($fecha."+ 1 days")); 
             
        echo $fecha;

        $array_out = $data;
        echo ($array_out);

            $array_out = array(
            0 => $info[$fecha]['compra'], 
            1 => $info[$fecha]['venta'],
            );
            echo json_encode($array_out);

        echo ($array_out[0]);
        return $array_out;
    }

}
?>