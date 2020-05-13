<?php
include_once("../system/conexion.php");
include_once("../system/funciones.php");

$conexion = new conexion();
$conexion->conexion();

/*
    Aqui estraigo la cabecera de
    la tabla accesos para traer los permisos
*/
$resultNameColumn = $conexion->consulta("
    SELECT * FROM sys_acceso
", "r");
$nameColumn = array();
for ($f = 0; $f < $resultNameColumn->columnCount(); $f++) {
    if (
        $resultNameColumn->getColumnMeta($f)['name'] != 'idaccesos' &&
        $resultNameColumn->getColumnMeta($f)['name'] != 'usuario' &&
        $resultNameColumn->getColumnMeta($f)['name'] != 'usuario_actualizar' &&
        $resultNameColumn->getColumnMeta($f)['name'] != 'fecha_actualizar'
    ) {
        $nameColumn[] = $resultNameColumn->getColumnMeta($f)['name'];
    }
}


/**
 * Itero sobre cada un para obtener sus padres e hijo y sus respectivos permisos
 * 
 */
$resultMenu = $conexion->consulta("
    SELECT idmenu, nombre
    FROM sys_menu
    WHERE estado = 'A'
    AND es_menu = 'S'
    ORDER BY orden
", "r");
$accesos = array();
$cont = 0;
foreach ($resultMenu as $filaMenu) {
    $accesos[$cont]['idmenu'] = $filaMenu['idmenu'];
    $accesos[$cont]['nombre'] = ucfirst($filaMenu['nombre']);
    $accesos[$cont]['submenu'] = array();

    $resultMenuInt = $conexion->consulta("
        SELECT idmenu, nombre
        FROM sys_menu
        WHERE idpadre = " . $filaMenu['idmenu'] . "
        AND es_menu = 'N'
        AND estado = 'A'
        ORDER BY orden
    ", "r");
    $contInt = 0;
    foreach ($resultMenuInt as $filaMenuInt) {
        $accesos[$cont]['submenu'][$contInt]['idmenu'] = $filaMenuInt['idmenu'];
        $accesos[$cont]['submenu'][$contInt]['nombre'] = ucfirst($filaMenuInt['nombre']);
        $accesos[$cont]['submenu'][$contInt]['permisos'] = array();

        $resultMenuAcceso = $conexion->consulta("
            SELECT *
            FROM sys_acceso
            WHERE idaccesos = '" . $filaMenuInt['idmenu'] . "'
        ", "r");
        $contAcces = 0;
        foreach ($resultMenuAcceso as $filaAcceso) {

            for ($i = 0; $i < count($nameColumn); $i++) {
                $accesos[$cont]['submenu'][$contInt]['permisos'][$nameColumn[$i]] = $filaAcceso[$nameColumn[$i]];
            }
            $contAcces++;
        }
        $contInt++;
    }

    $cont++;
}

print_r(FUNCIONES::json_respuesta(1, "", $accesos));
// echo "<pre>";
// print_r($accesos);
// echo "</pre>";