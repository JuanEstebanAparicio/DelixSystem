<?php
// DelixSystem/app/pages/gestor_reportes/php/ReportModel.php
class ReportModel {
    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    public function getDailyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ? AND pagado = 1 AND DATE(created_at) = CURRENT_DATE
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getWeeklyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ? AND pagado = 1 AND
                  DATE(created_at) >= (CURRENT_DATE - INTERVAL '7 days')
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ? AND pagado = 1 AND
                  EXTRACT(MONTH from created_at) = EXTRACT(MONTH from CURRENT_DATE)
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReportByRange($id_user, $fecha_inicio, $fecha_fin){
    $stmt = $this->db->prepare("
        SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
        FROM orders
        WHERE id_user = ? AND pagado = 1
              AND DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$id_user, $fecha_inicio, $fecha_fin]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


public function getDailyDetail($id_user, $inicio = null, $fin = null){
    if($inicio && $fin){
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS fecha, COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = :id_user AND pagado = 1
            AND DATE(created_at) BETWEEN :inicio AND :fin
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        $stmt->execute([
            ':id_user' => $id_user,
            ':inicio' => $inicio,
            ':fin' => $fin
        ]);
    } else {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS fecha, COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = :id_user AND pagado = 1
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) DESC
            LIMIT 7
        ");
        $stmt->execute([':id_user' => $id_user]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

   public function getReportesFiltrados($id_user, $fecha_inicio, $fecha_fin, $area) {
        global $conexion;

        $sql = "SELECT * FROM orders 
                WHERE id_user = :id_user
                AND pagado = TRUE";

        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha) >= :fecha_inicio";
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha) <= :fecha_fin";
        }
        if (!empty($area)) {
            $sql .= " AND area = :area";
        }

        $sql .= " ORDER BY fecha DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_user', $id_user);

        if (!empty($fecha_inicio)) $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        if (!empty($fecha_fin)) $stmt->bindParam(':fecha_fin', $fecha_fin);
        if (!empty($area)) $stmt->bindParam(':area', $area);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public function getReportesPorRango($id_user, $fecha_inicio, $fecha_fin, $area) {
        global $conexion;
        $query = "
            SELECT DATE(fecha) AS fecha, area, mesa, SUM(total) AS total
            FROM orders
            WHERE id_user = :id_user
              AND pagado = 't'
        ";

        if (!empty($fecha_inicio)) $query .= " AND fecha >= :fecha_inicio";
        if (!empty($fecha_fin)) $query .= " AND fecha <= :fecha_fin";
        if (!empty($area)) $query .= " AND area = :area";

        $query .= " GROUP BY DATE(fecha), area, mesa ORDER BY fecha DESC";

        $stmt = $conexion->prepare($query);
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        if (!empty($fecha_inicio)) $stmt->bindValue(':fecha_inicio', $fecha_inicio);
        if (!empty($fecha_fin)) $stmt->bindValue(':fecha_fin', $fecha_fin);
        if (!empty($area)) $stmt->bindValue(':area', $area);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

