<?php
// DelixSystem/app/pages/gestor_reportes/php/ReportController.php

require_once __DIR__ . '/ReportModel.php';

class ReportController {

    private $model;

    public function __construct($conexion){
        $this->model = new ReportModel($conexion);
    }

    /* ---------------------------------------------
     *  VALIDACIÓN CENTRALIZADA
     --------------------------------------------- */
    private function validarIdUser($id_user) {
        if (empty($id_user)) {
            throw new Exception("ID de usuario no definido. Verifique sesión (propietario o empleado).");
        }
    }

    /* ---------------------------------------------
     *  REPORTES PRINCIPALES
     --------------------------------------------- */
    public function obtenerReportes($id_user){
        $this->validarIdUser($id_user);

        return [
            "daily"   => $this->model->getDailyReport($id_user),
            "weekly"  => $this->model->getWeeklyReport($id_user),
            "monthly" => $this->model->getMonthlyReport($id_user)
        ];
    }

    public function obtenerReportePorRango($id_user, $inicio, $fin){
        $this->validarIdUser($id_user);
        return $this->model->getReportByRange($id_user, $inicio, $fin);
    }

    public function obtenerDetalleDiario($id_user, $inicio = null, $fin = null){
        $this->validarIdUser($id_user);
        return $this->model->getDailyDetail($id_user, $inicio, $fin);
    }

    public function obtenerReportesFiltrados($id_user, $fecha_inicio, $fecha_fin, $area) {
        $this->validarIdUser($id_user);
        return $this->model->getReportesFiltrados($id_user, $fecha_inicio, $fecha_fin, $area);
    }

    public function obtenerReportesPorRango($id_user, $fecha_inicio, $fecha_fin, $area) {
        $this->validarIdUser($id_user);
        return $this->model->getReportesPorRango($id_user, $fecha_inicio, $fecha_fin, $area);
    }

    /* ---------------------------------------------
     *  GRÁFICAS
     --------------------------------------------- */
    public function ventasUltimos7Dias($id_user){
        $this->validarIdUser($id_user);
        return $this->model->getVentasUltimos7Dias($id_user);
    }

    public function pedidosPorArea($id_user){
        $this->validarIdUser($id_user);

        $data = $this->model->getPedidosPorArea($id_user);

        return array_map(function ($row) {
            return [
                "area"    => $row["area"],
                "pedidos" => $row["total_pedidos"]
            ];
        }, $data);
    }

    public function ventasMesActual($id_user){
        $this->validarIdUser($id_user);
        return $this->model->getVentasMesActual($id_user);
    }

    public function topProductos($id_user, $limite = 5){
        $this->validarIdUser($id_user);
        return $this->model->getTopProductos($id_user, $limite);
    }

}
