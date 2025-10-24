<?php
require_once __DIR__ . '/RolesModel.php';

class RolesController {
    private $model;

    public function __construct() {
        $this->model = new RolesModel();
    }

    public function listarRoles() {
        return $this->model->getAllRoles();
    }

    public function listarRolesPorEmpleado($empleadoId) {
        return $this->model->getRolesByEmployee($empleadoId);
    }
}
