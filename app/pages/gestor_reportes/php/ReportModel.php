<?php

class ReportModel {

    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    /* ==========================================================
       REPORTES PRINCIPALES
    ========================================================== */

    public function getDailyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ? 
            AND pagado = 1 
            AND DATE(created_at) = CURRENT_DATE
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getWeeklyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ?
            AND pagado = 1 
            AND created_at >= CURRENT_DATE - INTERVAL '7 days'
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyReport($id_user){
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = ?
            AND pagado = 1
            AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        ");
        $stmt->execute([$id_user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ==========================================================
       REPORTES POR RANGO (con soporte para área)
    ========================================================== */
    public function getReportByRange($id_user, $inicio, $fin, $area = null){
        $sql = "
            SELECT 
                COUNT(*) AS total_orders,
                COALESCE(SUM(total_pedido),0) AS total_sales
            FROM orders
            WHERE id_user = :id_user
            AND pagado = 1
            AND DATE(created_at) BETWEEN :inicio AND :fin
        ";

        if ($area) $sql .= " AND area = :area";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":id_user", $id_user);
        $stmt->bindValue(":inicio", $inicio);
        $stmt->bindValue(":fin", $fin);

        if ($area) $stmt->bindValue(":area", $area);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /* ==========================================================
       DETALLE DIARIO
    ========================================================== */
    public function getDailyDetail($id_user, $inicio = null, $fin = null){
        if($inicio && $fin){
            $stmt = $this->db->prepare("
                SELECT DATE(created_at) AS fecha, COUNT(*) AS total_orders, COALESCE(SUM(total_pedido),0) AS total_sales
                FROM orders
                WHERE id_user = :id_user 
                AND pagado = 1
                AND DATE(created_at) BETWEEN :inicio AND :fin
                GROUP BY DATE(created_at)
                ORDER BY fecha ASC
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
                WHERE id_user = :id_user 
                AND pagado = 1
                GROUP BY DATE(created_at)
                ORDER BY fecha DESC
                LIMIT 7
            ");

            $stmt->execute([':id_user' => $id_user]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ==========================================================
       REPORTES POR RANGO AVANZADOS
    ========================================================== */
    public function getReportesPorRango($id_user, $inicio, $fin, $area){
        $sql = "
            SELECT 
                DATE(created_at) AS fecha,
                area,
                mesa,
                COALESCE(SUM(total_pedido),0) AS total
            FROM orders
            WHERE id_user = :id_user
            AND pagado = 1
        ";

        if ($inicio) $sql .= " AND DATE(created_at) >= :inicio";
        if ($fin)    $sql .= " AND DATE(created_at) <= :fin";
        if ($area)   $sql .= " AND area = :area";

        $sql .= " GROUP BY DATE(created_at), area, mesa
                  ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(":id_user", $id_user);

        if ($inicio) $stmt->bindValue(":inicio", $inicio);
        if ($fin)    $stmt->bindValue(":fin", $fin);
        if ($area)   $stmt->bindValue(":area", $area);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ==========================================================
       GRAFICAS
    ========================================================== */

    public function getVentasUltimos7Dias($id_user){
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS fecha, COALESCE(SUM(total_pedido), 0) AS total
            FROM orders
            WHERE id_user = :id_user
            AND created_at >= NOW() - INTERVAL '7 days'
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ");
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidosPorArea($id_user){
        $stmt = $this->db->prepare("
            SELECT area, COUNT(*) AS total_pedidos
            FROM orders
            WHERE id_user = :id_user
            GROUP BY area
            ORDER BY total_pedidos DESC
        ");
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentasMesActual($id_user){
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS fecha, COALESCE(SUM(total_pedido), 0) AS total
            FROM orders
            WHERE id_user = :id_user
            AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ");
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProductos($id_user, $limite = 5){
        $stmt = $this->db->prepare("
            SELECT 
                oi.nombre_platillo AS producto,
                SUM(oi.cantidad) AS total_vendidos
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE o.id_user = :id_user
            GROUP BY oi.nombre_platillo
            ORDER BY total_vendidos DESC
            LIMIT :limite
        ");

        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
