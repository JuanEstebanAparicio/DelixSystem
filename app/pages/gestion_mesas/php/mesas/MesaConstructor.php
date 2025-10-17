<?php
require_once __DIR__ . '/MesaModel.php';
require_once __DIR__ . '/../../../../config/supabase.php';

class MesaConstructor {
    private $conexion;
    private $mesaModel;

    public function __construct() {
        global $conexion;

        if (!$conexion instanceof PDO) {
            throw new Exception("Error: Conexión inválida (no es una instancia PDO).");
        }

        $this->conexion = $conexion;
        $this->mesaModel = new MesaModel($this->conexion);
    }

    public function getModel() {
        return $this->mesaModel;
    }
}
?>
