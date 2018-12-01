<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
/**
 *
 */
class ApiController extends REST_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('pago');
        $this->load->model('cambioMoneda');
    }

    public function cambio_get(){
        $fecha = $this->get('fecha');
        $array_out = $this->cambioMoneda->cambiarASoles($fecha);
        $this->response($array_out);
    }
    public function index_get(){
        $fecha_inicio = $this->get('inicio');
        $fecha_fin = $this->get('fin');
        $conceptos = $this->get('conceptos');
        if($fecha_inicio == '' || $fecha_fin == ''){
            $array_out = array("result"=>"error index");
        }
        else{
            $array_out = $this->pago->listarPorFechasCantidad($fecha_inicio, $fecha_fin,$conceptos);
        }
        $this->response($array_out);
    }

    public function importe_get(){
        $fecha_inicio = $this->get('inicio');
        $fecha_fin = $this->get('fin');
        $conceptos = $this->get('conceptos');
        if($fecha_inicio == '' || $fecha_fin == ''){
            $array_out = array("result"=>"error importe");
        }
        else{
            $array_out = $this->pago->listarPorFechasImporte($fecha_inicio, $fecha_fin,$conceptos);
        }
        $this->response($array_out);
    }

    public function devolverAnioImporte_get(){
        $year = $this->get("year");
        $conceptos = $this->get("conceptos");
        if($year == ""){
            $array_out = array("result"=>"error devolver anio importe");
        }
        else{
            $array_out = $this->pago->listarAnioImporte($year,$conceptos);
        }
        $this->response($array_out);
    }
    public function devolverAnioCantidad_get(){
        $year = $this->get("year");
        $conceptos = $this->get("conceptos");
        if($year == ""){
            $array_out = array("result"=>"error devolver anio cantidad");
        }
        else{
            $array_out = $this->pago->listarAnioCantidad($year,$conceptos);
        }
        $this->response($array_out);
    }

    public function tablaFechas_get(){
        $fecha_inicio = $this->get('inicio');
        $fecha_fin = $this->get('fin');
        $conceptos = $this->get("conceptos");
        if($fecha_inicio == '' || $fecha_fin == ''){
            $array_out = array("result"=>"error tabla fecha");
        }
        else{
            $array_out = $this->pago->registrosPorFechas($fecha_inicio, $fecha_fin,$conceptos);
        }
        $this->response($array_out);
    }
    public function tablaSemestre_get(){
        $conceptos = $this->get("conceptos");
        $ciclo = $this->get('ciclo');
        $cicloForma = $this->get('cicloForma');
        if($ciclo == '' || $cicloForma == ''){
            $array_out = array("result"=>"error tabla semestre");
        }
        else{
            $array_out = $this->pago->registrosPorSemestre($conceptos, $ciclo, $cicloForma);
        }
        $this->response($array_out);
    }

    public function tablaYear_get(){
        $year_inicio = $this->get("year_inicio");
        $year_fin = $this->get("year_fin");
        $conceptos = $this->get("conceptos");
        if($year_inicio == "" || $year_fin == "" ){
            $array_out = array("result"=>"error tabla años");
        }
        else{
            $array_out = $this->pago->registrosPorAnio($year_inicio, $year_fin,$conceptos);
        }
        $this->response($array_out);
    }

    public function tablaMonth_get(){
        $year = $this->get("year");
        $startMonth = $this->get("mes_inicio");
        $endMonth = $this->get("mes_fin");
        $conceptos = $this->get("conceptos");

        if($startMonth == "" or $endMonth == "" or $year == ""){
            $data = array('result'=>'error tabla mes');
        }
        else if($startMonth > $endMonth){
            $data = array('result'=>'error tabla');
        }
        else{
            $data = $this->pago->registrosPorMes($year,$startMonth,$endMonth, $conceptos);
        }
        $this->response($data);
    }

        //cambios diego
        public function listaConceptos_get(){
            $array_out = $this->pago->listarConceptos();
            $this->response($array_out);
        }

        public function cantidadPorPeriodoAnio_get(){
            $yearStart = $this->get("year_inicio");
            $yearEnd = $this->get("year_fin");
            $conceptos = $this->get("conceptos");

            if($yearStart == "" or $yearEnd == ""){
                $data = array('result'=>'error cantidad por periodo');
            }
            else if($yearStart > $yearEnd){
                $data = array('result'=>'error cantidad por periodo');
            }
            else{
                $data = $this->pago->listarCantidadPeriodoAnual($yearStart,$yearEnd, $conceptos);
            }
            $this->response($data);
        }

        public function montoPorPeriodoAnio_get(){
            $yearStart = $this->get("year_inicio");
            $yearEnd = $this->get("year_fin");
            $conceptos = $this->get("conceptos");

            if($yearStart == "" or $yearEnd == ""){
                $data = array('result'=>'error monto por periodo');
            }
            else if($yearStart > $yearEnd){
                $data = array('result'=>'error monto por periodo');
            }
            else{
                $data = $this->pago->listarTotalPeriodoAnual($yearStart,$yearEnd, $conceptos);
            }
            $this->response($data);
        }

        public function cantidadPorPeriodoMes_get(){
            $year = $this->get("year");
            $startMonth = $this->get("mes_inicio");
            $endMonth = $this->get("mes_fin");
            $conceptos = $this->get("conceptos");

            if($startMonth == "" or $endMonth == "" or $year == ""){
                $data = array('result'=>'error cantidad por periodo mes');
            }
            else if($startMonth > $endMonth){
                $data = array('result'=>'error cantidad por periodo mes');
            }
            else{
                $data = $this->pago->listarCantidadPeriodoMensual($year,$startMonth,$endMonth , $conceptos);
            }
            $this->response($data);
        }
        public function totalPorPeriodoMes_get(){
            $year = $this->get("year");
            $startMonth = $this->get("mes_inicio");
            $endMonth = $this->get("mes_fin");
            $conceptos = $this->get("conceptos");

            if($startMonth == "" or $endMonth == "" or $year == ""){
                $data = array('result'=>'error total periodo mes');
            }
            else if($startMonth > $endMonth){
                $data = array('result'=>'error total periodo mes');
            }
            else{
                $data = $this->pago->listarTotalPeriodoMensual($year,$startMonth,$endMonth, $conceptos);
            }
            $this->response($data);
        }
}


 ?>
