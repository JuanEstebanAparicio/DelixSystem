<?php


$basePath = '/DelixSystem/app';


require_once __DIR__ . '/../../components/header_empleado.php';
require_once __DIR__ . '/../../components/control_center.php';


echo <<<HTML

  <link rel="stylesheet" href="{$basePath}/shared/css/globals.css">
  <link rel="stylesheet" href="{$basePath}/shared/css/control_center.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <script src="{$basePath}/shared/js/control_center.js"></script>
HTML;
