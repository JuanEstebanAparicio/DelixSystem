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

    /* ==========================================================
   📊 GRAFICA 1 — Ventas últimos 7 días
========================================================== */
public function getVentasUltimos7Dias($id_user)
{
    $query = "
        SELECT 
            DATE(created_at) AS fecha,
            COALESCE(SUM(total_pedido), 0) AS total
        FROM orders
        WHERE id_user = :id_user
        AND created_at >= NOW() - INTERVAL '7 days'
        GROUP BY DATE(created_at)
        ORDER BY fecha ASC;
    ";

    $stmt = $this->db->prepare($query);
    $stmt->execute(['id_user' => $id_user]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



/* ==========================================================
   📊 GRAFICA 2 — Pedidos agrupados por área
========================================================== */
public function getPedidosPorArea($id_user)
{
    $query = "
        SELECT 
            area,
            COUNT(*) AS total_pedidos
        FROM orders
        WHERE id_user = :id_user
        GROUP BY area
        ORDER BY total_pedidos DESC;
    ";

    $stmt = $this->db->prepare($query);
    $stmt->execute(['id_user' => $id_user]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



/* ==========================================================
   📊 GRAFICA 3 — Ventas del mes actual (por día)
========================================================== */
public function getVentasMesActual($id_user)
{
    $query = "
        SELECT 
            DATE(created_at) AS fecha,
            COALESCE(SUM(total_pedido), 0) AS total
        FROM orders
        WHERE id_user = :id_user
        AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        GROUP BY DATE(created_at)
        ORDER BY fecha ASC;
    ";

    $stmt = $this->db->prepare($query);
    $stmt->execute(['id_user' => $id_user]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getTopProductos($id_user, $limite = 5)
{
    $query = "
        SELECT 
            oi.nombre_platillo AS producto,
            SUM(oi.cantidad) AS total_vendidos
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE o.id_user = :id_user
        GROUP BY oi.nombre_platillo
        ORDER BY total_vendidos DESC
        LIMIT :limite
    ";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}

