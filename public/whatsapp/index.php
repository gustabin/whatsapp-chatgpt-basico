<?php
// desabilitamos el mostrar errores
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(-1);

if ($_GET) {
    // VERIFICACION DEL WEBHOOK
    //TOQUEN QUE QUERRAMOS PONER 
    $token = 'HolaNovato';
    //RETO QUE RECIBIREMOS DE FACEBOOK
    $palabraReto = $_GET['hub_challenge'];
    //TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
    $tokenVerificacion = $_GET['hub_verify_token'];
    //SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
    if ($token === $tokenVerificacion) {
        echo $palabraReto;
        exit;
    }
}

$textoCita = "He creado su cita para el dia";
/*
 * RECEPCION DE MENSAJES
 */
//LEEMOS LOS DATOS ENVIADOS POR WHATSAPP
$respuesta = file_get_contents("php://input");
//CONVERTIMOS EL JSON EN ARRAY DE PHP
$respuesta = json_decode($respuesta, true);
//EXTRAEMOS EL MENSAJE DEL ARRAY
if (isset($respuesta['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])) {
    $mensaje = $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
}
if (isset($respuesta['entry'][0]['changes'][0]['value']['messages'][0]['from'])) {
    //EXTRAEMOS EL TELEFONO DEL ARRAY
    $telefonoCliente = $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['from'];
}
if (isset($respuesta['entry'][0]['changes'][0]['value']['messages'][0]['id'])) {
    //EXTRAEMOS EL ID DE WHATSAPP DEL ARRAY
    $id = $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['id'];
}
if (isset($respuesta['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'])) {
    //EXTRAEMOS EL TIEMPO DE WHATSAPP DEL ARRAY
    $timestamp = $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
}
//SI HAY UN MENSAJE
if (isset($mensaje)) {
    $pregunta = $mensaje;
    require_once("conexion.php");
    // Prepara la consulta SQL
    $queryCompania = 'SELECT company, address, contact FROM kimai2_invoice_templates WHERE id = ?';
    $stmtCompania = $conn->prepare($queryCompania);
    // Vincula el parámetro "id" a la consulta SQL
    $id_inv = 1;
    $stmtCompania->bind_param('i', $id_inv);
    // Ejecuta la consulta
    $stmtCompania->execute();
    // Obtiene el resultado
    $resultCompania = $stmtCompania->get_result();

    // Comprueba si la consulta devolvió resultados antes de intentar acceder a los datos
    if ($resultCompania && $resultCompania->num_rows > 0) {
        // Obtiene los datos del registro
        $rowCompania = $resultCompania->fetch_assoc();

        // Ahora puedes acceder a los valores en $rowCompania sin riesgo de advertencias
        $company = $rowCompania['company'];
        $address = $rowCompania['address'];
        $contact = $rowCompania['contact'];
    } else {
        // La consulta no devolvió resultados, puedes manejar este caso según tus necesidades
        echo "No se encontraron resultados.";
    }

    // Si es necesario, cierra el statement y la conexión después de usarlos
    $stmtCompania->close();
    // Prepara la consulta SQL
    // Crea la consulta SQL
    $queryCategoria = "SELECT alias FROM kimai2_users WHERE enabled = 1 AND id != 1";
    // Prepara la consulta
    $stmtCategoria = $conn->prepare($queryCategoria);
    // Ejecuta la consulta
    $stmtCategoria->execute();
    // Obtiene el resultado
    $resultCategoria = $stmtCategoria->get_result();
    // Crea un array para guardar los alias
    $listaCategorias = "";
    // Recorre los resultados y añade cada alias al array
    while ($row = $resultCategoria->fetch_assoc()) {
        $listaCategorias = $listaCategorias . $row['alias'] . ",";
    }
    $listaCategorias = rtrim($listaCategorias, ",");

    $system = "Hola, soy un asistente de Información de la Clínica $company. Mi función es generar reportes 
        con los detalles proporcionados para que un experto posteriormente pueda agendar citas. 
        A continuación, se describe el procedimiento que sigo:

        1. Comienzo solicitando el nombre del paciente.
        2. Luego, pido la edad del paciente.
        3. Después, indago sobre los síntomas que el paciente está experimentando.
        No solicito ningún otro dato.
        
        Basado en los síntomas recolectados, clasifico al paciente en una de las siguientes especialidades: 
            $listaCategorias. Si no encaja en ninguna especialidad, lo clasifico como Médico General.
        
        Una vez que tengo los síntomas y la especialidad, genero un reporte con el siguiente formato:
        ||nombre||edad||especialidad||sintomas||
        
        Es crucial que siempre que se detecten síntomas, el reporte siga el formato mencionado para que un experto 
        pueda agendar una cita adecuada. Importante destacar que no añado información que no se me haya proporcionado, 
        y mi trabajo se basa en los datos recibidos.";

    // Solo proporciono información sobre la empresa si el paciente la solicita. Nombre de la empresa: $company, 
    // Ubicación: $address, Contácto: $contact.
    // ";


    file_put_contents("system.txt", $system);
    require_once "chatgpt.php";
    $respuesta = preguntaChatgpt($system, $pregunta, $telefonoCliente, $listaCategorias);
    //ESCRIBIMOS LA RESPUESTA
    file_put_contents("respuesta.txt", $respuesta);

    require_once "whatsapp.php";
    //ENVIAMOS LA RESPUESTA VIA WHATSAPP
    enviar($mensaje, $respuesta, $id, $timestamp, $telefonoCliente);
}
