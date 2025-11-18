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
 
    /* ================================
       📊 FUNCIONES DE GRÁFICAS
    ================================= */

    // 🔹 Ventas últimos 7 días
    public function ventasUltimos7Dias($id_user){
        return $this->model->getVentasUltimos7Dias($id_user);
    }

    // 🔹 Pedidos por área
    public function pedidosPorArea($id_user){
        // Ajustamos el nombre para que coincida con el JS (pedidos en vez de total_pedidos)
        $data = $this->model->getPedidosPorArea($id_user);

        return array_map(function ($row) {
            return [
                "area" => $row["area"],
                "pedidos" => $row["total_pedidos"]  // JS usa "pedidos"
            ];
        }, $data);
    }

    // 🔹 Ventas del mes actual
    public function ventasMesActual($id_user){
        return $this->model->getVentasMesActual($id_user);
    }

    // 🔹 Top productos más vendidos
public function topProductos($id_user, $limite = 5){
    return $this->model->getTopProductos($id_user, $limite);
}

    
}
