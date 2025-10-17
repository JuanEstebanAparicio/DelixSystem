<?php
require_once __DIR__ . '/AreaModel.php';
require_once __DIR__ . '/../../../../config/supabase.php';

class AreaConstructor {
    private $conexion;
    private $areaModel;

    public function __construct() {
        global $conexion;

        if (!$conexion instanceof PDO) {
            throw new Exception("Error: Conexión inválida (no es una instancia PDO).");
        }

        $this->conexion = $conexion;
        $this->areaModel = new AreaModel($this->conexion);
    }

    public function getModel() {
        return $this->areaModel;
    }
}
?>
