<?php
include_once "../system/conexion.php";
include_once "../system/funciones.php";

$conexion = new conexion();
$conexion->conexion();

/******************************************************************************************/
/*
    Aqui estraigo la cabecera de
    la tabla accesos para traer los permisos
*/
$resultNameColumn = $conexion->consulta("
SELECT * FROM sys_acceso
", "r");
$nameColumn = array();
$cab_column = array();
for ($f = 0; $f < $resultNameColumn->columnCount(); $f++) {
    if (
        $resultNameColumn->getColumnMeta($f)['name'] != 'usuario_actualizar' &&
        $resultNameColumn->getColumnMeta($f)['name'] != 'fecha_actualizar'
    ) {
        $nameColumn[] = $resultNameColumn->getColumnMeta($f)['name'];
    }
}
foreach ($nameColumn as $key => $value) {
    $cab_column[] = "$value";
}
array_push($cab_column, "usuario_actualizar", "fecha_actualizar");
$cab_column = '(' . implode(",", $cab_column) . ')';
/******************************************************************************************/

/*Declaraciones de variables de uso del modulo */
$respuesta = null;

$usuario = null;
$menuCheckeado = array();
/******************************************************************************************/

/*Pregunto si estan establecida las variables y si una de ellas en distinto de vacios*/
if (
    isset($_POST['usuario']) && !empty($_POST['usuario'])
) {
    $usuario = strtolower(addslashes($_POST['usuario']));
}

if (isset($_POST['menuChekeado'])) {
    $menuCheckeado = $_POST['menuChekeado'];
}
/******************************************************************************************/

/**Pregunto si la variable usuario esta vacio */
if (empty($usuario)) {
    $respuesta = FUNCIONES::json_respuesta(2, "Parametros Vacios");
}
/******************************************************************************************/


/**Elimina el usuario previamente al guardar los permioso */
$eleminar_usuario = $conexion->consulta("DELETE FROM sys_acceso WHERE usuario = :usuario");
$eleminar_usuario->bindValue(":usuario", $usuario);
if ($eleminar_usuario->execute() != true) {
    $respuesta = FUNCIONES::json_respuesta(2, "Error al procesar");
}
/******************************************************************************************/

if (count($menuCheckeado) > 0) {

    $todos_values = [];
    /**estraigo el cabecera de las columnas para guardar los permisos
     * donde los estraigo la cabeceras desde el script q se encueentras al principio de gestion.php
     * ($cab_column) 
     */
    $query = "INSERT INTO sys_acceso $cab_column  VALUES ";

    /**
     * como es un array bidimensional lo itero extrayendo el arreglo q se encuentra en el primero arreglo
     * i extreyendo los valor de cada una de si posicion
     * por ultimo añado dos valores mas y seguidamente lo guardo en parentesis y las comillas simple o dobles
     * luego lo añado el $query con si insert añadiendole comas
     * por ultimo los ejecuto y aguardo en la base de datos
     */
    foreach ($menuCheckeado as $key) {
        $fila_value = [];
        foreach ($key as $skey => $svalue) {
            $fila_value[] = "'" . $svalue . "'";
        }
        array_push($fila_value, '"session_usuario"', 'now()');
        $todos_values[] = '(' . implode(',', $fila_value) . ')';
    }
    $query .= implode(",", $todos_values);
}

$consulta = $conexion->consulta($query);
if ($consulta->execute()) {
    print_r("true");
} else {
    print_r($consulta->errorInfo()[2]);
}

    // print_r($query);
