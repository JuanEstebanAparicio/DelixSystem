<?php
// DelixSystem/app/pages/gestor_reportes/php/ReportController.php

require_once __DIR__ . '/ReportModel.php';

class ReportController {

    private $model;

    public function __construct($conexion){
        $this->model = new ReportModel($conexion);
    }

    public function obtenerReportes($id_user){
        $daily = $this->model->getDailyReport($id_user);
        $weekly = $this->model->getWeeklyReport($id_user);
        $monthly = $this->model->getMonthlyReport($id_user);

        return [
            "daily" => $daily,
            "weekly" => $weekly,
            "monthly" => $monthly
        ];
    }

    public function obtenerReportePorRango($id_user, $inicio, $fin){
    return $this->model->getReportByRange($id_user, $inicio, $fin);
}

public function obtenerDetalleDiario($id_user, $inicio = null, $fin = null){
    return $this->model->getDailyDetail($id_user, $inicio, $fin);
}

    public function obtenerReportesFiltrados($id_user, $fecha_inicio, $fecha_fin, $area) {
        return $this->model->getReportesFiltrados($id_user, $fecha_inicio, $fecha_fin, $area);
    }

        public function obtenerReportesPorRango($id_user, $fecha_inicio, $fecha_fin, $area) {
        return $this->model->getReportesPorRango($id_user, $fecha_inicio, $fecha_fin, $area);
    }
    
}
