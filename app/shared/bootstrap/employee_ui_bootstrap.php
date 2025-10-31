<?php
/**
 * Carga los componentes y recursos globales del panel de empleado.
 * Uso:
 *   include __DIR__ . '/../../../shared/bootstrap/employee_ui_bootstrap.php';
 */

$basePath = '/DelixSystem/app';

// 🔹 Incluir componentes PHP reutilizables
require_once __DIR__ . '/../../components/header_empleado.php';
require_once __DIR__ . '/../../components/control_center.php';

// 🔹 Imprimir automáticamente los <link> y <script> globales
echo <<<HTML
  <!-- 🔸 Recursos globales del panel empleado -->
  <link rel="stylesheet" href="{$basePath}/shared/css/globals.css">
  <link rel="stylesheet" href="{$basePath}/shared/css/control_center.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="{$basePath}/shared/js/control_center.js"></script>
HTML;
