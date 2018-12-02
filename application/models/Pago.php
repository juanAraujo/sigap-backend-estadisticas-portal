<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Pago extends CI_Model
{
    function __construct(){
        parent::__construct();
        $this->load->model('cambioMoneda');
    }

    public function listarTodosCantidad (){
        /*$this->db->select('concepto');
        $this->db->from('pago');
        $this->db->group_by('concepto');*/
        $query = $this->db->query('SELECT concepto, COUNT(concepto) AS cantidad FROM pago GROUP BY pago.concepto');
        $data = $query->result_array();
        $array_out = array('labels'=>array(),'datasets'=>array());
        $dataset = array('label'=>'transacciones','data'=>array());
        foreach ($data as $concepto) {
            $array_out['labels'][] = $concepto['concepto'];
            $dataset['data'][] = $concepto['cantidad'];
        }
        $array_out['datasets'][] = $dataset;
        return $array_out;
    }

    public function listarTodosImporte (){
        $query = $this->db->query('SELECT concepto, SUM(importe) AS cantidad FROM pago GROUP BY pago.concepto');
        $data = $query->result_array();
        $array_out = array('labels'=>array(),'datasets'=>array());
        $dataset = array('label'=>'Importe','data'=>array());
        foreach ($data as $concepto) {
            $array_out['labels'][] = $concepto['concepto'];
            $dataset['data'][] = $concepto['cantidad'];
        }
        $array_out['datasets'][] = $dataset;
        return $array_out;
    }

    public function listarPorFechasCantidad($fecha_inicio, $fecha_fin, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query("SELECT c.concepto AS concepto, COUNT(r.id_concepto) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            extract(epoch FROM r.fecha) >= ".$fecha_inicio."
            AND extract(epoch FROM r.fecha) <= ".$fecha_fin."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY r.id_concepto,c.concepto
        ORDER BY c.concepto");
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Importes');
        return $array_out;
    }

    public function listarPorFechasImporte($fecha_inicio, $fecha_fin, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query("SELECT c.concepto AS concepto, SUM(r.importe) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            extract(epoch FROM r.fecha) >= ".$fecha_inicio."
            AND extract(epoch FROM r.fecha) <= ".$fecha_fin."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY r.id_concepto,c.concepto
        ORDER BY c.concepto");
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Monto');
        return $array_out;
    }

    public function listarAnioCantidad($year, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
            "SELECT date_part('month',r.fecha) AS concepto,
                    COUNT(r.importe) AS cantidad
            FROM public.recaudaciones r
            INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
            INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
            WHERE (
                date_part('year',fecha) = ".$year."
                AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
                 ".$condicional."
            )
            GROUP BY date_part('month',r.fecha)"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Importes');
        $f_array_out = $this->formatoFecha($array_out);
        return $f_array_out;
    }
    public function test(){
        return "hola";
    }

    public function listarAnioImporte($year, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
            "SELECT date_part('month',r.fecha) AS concepto,
                    SUM(importe) AS cantidad
            FROM public.recaudaciones r
            INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
            INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
            WHERE (
                date_part('year',fecha) = ".$year."
                AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
                 ".$condicional."
            )
            GROUP BY date_part('month',r.fecha)"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Monto');
        $f_array_out = $this->formatoFecha($array_out);
        return $f_array_out;
    }

    public function registrosPorFechas($fecha_inicio, $fecha_fin,$conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }
        $query = $this->db->query("SELECT c.concepto AS concepto, r.importe AS importe, trim(a.codigo) AS codigoAlumno, a.ape_nom AS nombreAlumno, to_char(r.fecha,'DD-MM-YYYY') AS fecha
            FROM public.recaudaciones r
                INNER JOIN public.concepto c
                    ON (r.id_concepto = c.id_concepto)
                INNER JOIN public.alumno a
                    ON (r.id_alum = a.id_alum)
                INNER JOIN public.clase_pagos p
                    ON (p.id_clase_pagos = c.id_clase_pagos)
            WHERE (
                extract(epoch FROM r.fecha) >= ".$fecha_inicio."
                AND extract(epoch FROM r.fecha) <= ".$fecha_fin."
                AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
                 ".$condicional."
            )
            ORDER BY to_char(r.fecha,'YYYY-MM-DD')");
        $data = $query->result_array();
        $array_out = $this->formatoTabla($data);
        return $array_out;
    }
    public function registrosPorSemestre($conceptos, $ciclo, $cicloForma){

        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }
        $query = $this->db->query("SELECT p.sigla_programa AS programa, c.concepto AS concepto, to_char(SUM(r.importe),'99,999,990.00') AS importe FROM recaudaciones r 
            INNER JOIN concepto c ON (r.id_concepto=c.id_concepto)
            INNER JOIN alumno_alumno_programa aap ON (r.id_alum=aap.id_alum)
            INNER JOIN programa p ON (p.id_programa=aap.id_programa)
            INNER JOIN matricula_cab m ON (m.id_programa=p.id_programa)
            WHERE (r.fecha >= (SELECT fecha_inicio FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND r.fecha <= (SELECT fecha_fin FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND m.semestre='".$cicloForma."'
                 ".$condicional.")
            GROUP BY p.sigla_programa, c.concepto
            ORDER BY p.sigla_programa, c.concepto");
        
        $data = $query->result_array();
        $array_out = $this->formatoTablaSemestre($data);
            
        /*el primer query es el estandar de la tabla resultante en base a la cual se sabrá cuantas filas debe tener(programa-concepto) */
        /*se debe generar dos querys uno la suma de importes de soles y otro query con una lista de (programa-concepto-importe-fecha) siendo el importe en dolares*/
        /*a la tabla de dolares realizarle el cambio usando la api de la sunat*/
        /*tras los cambio de moneda se procede a crear la tabla final tomando como guia el resultado estandar se buscará los programa-concepto iguales 
            de las tablar en soles y dolares y se sumaran los importes y serán almacenados en el importe de la tabla estandar */
        /*finalmente tendremos la tabla estandar con el importe total en soles*/

        //query en soles
        $query = $this->db->query("SELECT p.sigla_programa AS programa, c.concepto AS concepto, SUM(r.importe) AS importe FROM recaudaciones r 
            INNER JOIN concepto c ON (r.id_concepto=c.id_concepto)
            INNER JOIN alumno_alumno_programa aap ON (r.id_alum=aap.id_alum)
            INNER JOIN programa p ON (p.id_programa=aap.id_programa)
            INNER JOIN matricula_cab m ON (m.id_programa=p.id_programa)
            WHERE (r.moneda='108' AND r.fecha >= (SELECT fecha_inicio FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND r.fecha <= (SELECT fecha_fin FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND m.semestre='".$cicloForma."'
                 ".$condicional.")
            GROUP BY p.sigla_programa, c.concepto
            ORDER BY p.sigla_programa, c.concepto");
        
        $data = $query->result_array();
        $array_out_soles = $this->formatoTablaSemestre($data);
        //echo json_encode($data);

        //query en dolares
        $query = $this->db->query("SELECT p.sigla_programa AS programa, c.concepto AS concepto, r.importe AS importe, r.fecha AS fecha FROM recaudaciones r 
            INNER JOIN concepto c ON (r.id_concepto=c.id_concepto)
            INNER JOIN alumno_alumno_programa aap ON (r.id_alum=aap.id_alum)
            INNER JOIN programa p ON (p.id_programa=aap.id_programa)
            INNER JOIN matricula_cab m ON (m.id_programa=p.id_programa)
            WHERE (r.moneda='113' AND r.fecha >= (SELECT fecha_inicio FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND r.fecha <= (SELECT fecha_fin FROM ciclo WHERE nom_ciclo='".$ciclo."') 
                AND m.semestre='".$cicloForma."'
                 ".$condicional.")
            ORDER BY p.sigla_programa, c.concepto");
        
        $data = $query->result_array();
        $array_out_dolares = $this->formatoTablaSemestre($data);
        //echo json_encode($array_out_dolares);
        //echo("************");
        $array_out_dolares = $this->cambiarASoles($array_out_dolares);
        //echo json_encode($array_out_dolares);
        //
        return $array_out;
    }
    public function cambiarASoles($array)
    {
        //echo("************");
        $array_out = array();
        echo($this->cambioMoneda->cambiarASoles($array[0]['fecha']));
        echo $array[0]['fecha'];
        /*foreach ($array as $registro) {
                echo $registro['importe'];
                echo("************");
                $cambio = $this->cambioMoneda->cambiarASoles($registro['fecha']);
                $cambio = $cambio +0;
                $registro['importe'] = $registro['importe'] * $cambio;
                
                echo $registro['importe'];
                echo("************");
                $array_out[] = $registro;
        }*/
        
        return $array_out;
    }
    public function registrosPorAnio($yearStart, $yearEnd ,$conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }
        $query=$this->db->query("SELECT c.concepto AS concepto, r.importe AS importe, trim(a.codigo) AS codigoAlumno, a.ape_nom AS nombreAlumno, to_char(r.fecha,'DD-MM-YYYY') AS fecha
            FROM public.recaudaciones r
                INNER JOIN public.concepto c
                    ON (r.id_concepto = c.id_concepto)
                INNER JOIN public.alumno a
                    ON (r.id_alum = a.id_alum)
                INNER JOIN public.clase_pagos p
                    ON (p.id_clase_pagos = c.id_clase_pagos)
            WHERE (
                date_part('year',r.fecha) between ".$yearStart." and ".$yearEnd."
                AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
                 ".$condicional."
            )
            ORDER BY to_char(r.fecha,'YYYY-MM-DD')");
        $data = $query->result_array();
        $array_out = $this->formatoTabla($data);
        return $array_out;
    }
    public function registrosPorMes ($year,$startMonth,$endMonth, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
        "SELECT c.concepto AS concepto,r.importe AS importe, trim(a.codigo) AS codigoAlumno, a.ape_nom AS nombreAlumno, to_char(r.fecha,'DD-MM-YYYY') AS fecha
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        INNER JOIN public.alumno a ON (r.id_alum = a.id_alum)
        WHERE (
            date_part('year',r.fecha) = ".$year."
            AND date_part('month',r.fecha) between ".$startMonth." and ".$endMonth."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        ORDER BY to_char(r.fecha,'YYYY-MM-DD')");

        $data = $query->result_array();
        $array_out = $this->formatoTabla($data);
        return $array_out;
    }

    //DE AÑO A OTRO A AÑO CANTIDAD/TOTAL
    public function listarCantidadPeriodoAnual($yearStart, $yearEnd, $conceptos){

        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }
        $query = $this->db->query(
        "SELECT date_part('year',r.fecha) AS concepto,COUNT(r.importe) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            date_part('year',r.fecha) between ".$yearStart." and ".$yearEnd."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY date_part('year',r.fecha);"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Cantidad');
        return $array_out;

    }
    public function listarTotalPeriodoAnual($yearStart, $yearEnd, $conceptos){

        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
        "SELECT date_part('year',r.fecha) AS concepto,SUM(r.importe) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            date_part('year',r.fecha) between ".$yearStart." and ".$yearEnd."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY date_part('year',r.fecha);"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Monto');
        return $array_out;

    }

    //AÑO->mes inicial y fina
    public function listarCantidadPeriodoMensual($year,$startMonth,$endMonth, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
        "SELECT date_part('month',r.fecha) AS concepto,
                COUNT(r.importe) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            date_part('year',r.fecha) = ".$year."
            AND date_part('month',r.fecha) between ".$startMonth." and ".$endMonth."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY date_part('month',r.fecha);"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Cantidad');
        $f_array_out = $this->formatoFecha($array_out);
        return $f_array_out;

    }

    public function listarTotalPeriodoMensual($year,$startMonth,$endMonth, $conceptos){
        if (trim($conceptos) != ""){
            $condicional = "AND c.concepto::integer in (".str_replace ("|",",",$conceptos).")";
        }
        else{
            $condicional = "";
        }

        $query = $this->db->query(
        "SELECT date_part('month',r.fecha) AS concepto,
                SUM(r.importe) AS cantidad
        FROM public.recaudaciones r
        INNER JOIN public.concepto c ON (r.id_concepto = c.id_concepto)
        INNER JOIN public.clase_pagos p ON (p.id_clase_pagos = c.id_clase_pagos)
        WHERE (
            date_part('year',r.fecha) = ".$year."
            AND date_part('month',r.fecha) between ".$startMonth." and ".$endMonth."
            AND p.id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S')
             ".$condicional."
        )
        GROUP BY date_part('month',r.fecha);"
        );
        $data = $query->result_array();
        $array_out = $this->formatoGrafico($data,'Cantidad');
        $f_array_out = $this->formatoFecha($array_out);
        return $f_array_out;

    }

    public function listarConceptos(){
        $query = $this->db->query(
            "select concepto from public.concepto where id_clase_pagos in (SELECT distinct (id_clase_pagos) FROM configuracion where estado = 'S' )"
        );
        $data = $query->result_array();
        return $this->formatoConceptos($data);
    }

    private function formatoGrafico($data,$etiqueta){
        $array_out = array('labels'=>array(),'datasets'=>array());
        $dataset = array('label'=>$etiqueta,'data'=>array());
        if(count($data)>0){
            foreach ($data as $concepto) {

                $array_out['labels'][] = $concepto['concepto'];
                $dataset['data'][] = $concepto['cantidad'];
            }

        }
        $array_out['datasets'][] = $dataset;
        return $array_out;
    }

    private function formatoTabla($data){
        $array_out = array();
        if(count($data)>0){
            foreach ($data as $registro) {
                $array_out[] = $registro;
            }
        }
        return $array_out;
    }
    private function formatoTablaSemestre($data){
        $array_out = array();
        if(count($data)>0){
            foreach ($data as $registro) {
                $array_out[] = $registro;
            }
        }
        return $array_out;
    }
    private function formatoFecha($data){
        if(count($data)>0){
            foreach($data["labels"] as $clave => $mes){
                if($mes == 1){
                    $data["labels"][$clave] = "Enero";
                } elseif($mes == 2){
                    $data["labels"][$clave] = "Febrero";
                }elseif($mes == 3){
                    $data["labels"][$clave] = "Marzo";
                }elseif($mes == 4){
                    $data["labels"][$clave] = "Abril";
                }elseif($mes == 5){
                    $data["labels"][$clave] = "Mayo";
                }elseif($mes == 6){
                    $data["labels"][$clave] = "Junio";
                }elseif($mes == 7){
                    $data["labels"][$clave] = "Julio";
                }elseif($mes == 8){
                    $data["labels"][$clave] = "Agosto";
                }elseif($mes == 9){
                    $data["labels"][$clave] = "Septiembre";
                }elseif($mes == 10){
                    $data["labels"][$clave] = "Octubre";
                }elseif($mes == 11){
                    $data["labels"][$clave] = "Noviembre";
                }elseif($mes == 12){
                    $data["labels"][$clave] = "Diciembre";
                }
            }
        }
        return $data;
    }

    private function formatoConceptos($data){
        $array_out = array("conceptos"=>array());
        if(count($data)>0){
            foreach ($data as $concepto) {
                $array_out['conceptos'][] = $concepto['concepto'];
            }
        }
        return $array_out;
    }

}



 ?>
