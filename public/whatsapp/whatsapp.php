<?php
//enviar.php
/*
 * RECIBIMOS LA RESPUESTA
*/
function enviar($recibido, $enviadoWa, $idWA, $timestamp, $telefonoCliente)
{
    global $conn;
    global $textoCita;
    //CONSULTAMOS TODOS LOS REGISTROS CON EL ID DEL MANSAJE
    $sqlCantidad = "SELECT count(id) AS cantidad FROM kimai2_registro WHERE id_wa='" . $idWA . "';";
    $resultCantidad = $conn->query($sqlCantidad);
    //OBTENEMOS LA CANTIDAD DE MENSAJES ENCONTRADOS (SI ES 0 LO REGISTRAMOS SI NO NO)
    $cantidad = 0;
    //SI LA CONSULTA ARROJA RESULTADOS
    if ($resultCantidad) {
        //OBTENEMOS EL PRIMER REGISTRO
        $rowCantidad = $resultCantidad->fetch_row();
        //OBTENEMOS LA CANTIDAD DE REGISTROS
        $cantidad = $rowCantidad[0];
    }
    //SI LA CANTIDAD DE REGISTROS ES 0 ENVIAMOS EL MENSAJE DE LO CONTRARIO NO LO ENVIAMOS PORQUE YA SE ENVIO
    if ($cantidad == 0) {
        $enviado = str_replace("\n", "", $enviadoWa);
        //TOKEN QUE NOS DA FACEBOOK
        $config = new Config();
        $token = $config->tokenWa;
        //NUESTRO TELEFONO
        $telefono = str_replace("54911", "541115", $telefonoCliente);
        //IDENTIFICADOR DE NÚMERO DE TELÉFONO
        $telefonoID = $config->telefonoIDWa;

        //URL A DONDE SE MANDARA EL MENSAJE
        $url = 'https://graph.facebook.com/v16.0/' . $telefonoID . '/messages';
        //CONFIGURACION DEL MENSAJE
        $mensaje = ''
            . '{'
            . '"messaging_product": "whatsapp", '
            . '"recipient_type": "individual",'
            . '"to": "' . $telefono . '", '
            . '"type": "text", '
            . '"text": '
            . '{'
            . '     "body":"' . $enviado . '",'
            . '     "preview_url": true, '
            . '} '
            . '}';
        //DECLARAMOS LAS CABECERAS
        $header = array("Authorization: Bearer " . $token, "Content-Type: application/json",);
        //INICIAMOS EL CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //OBTENEMOS LA RESPUESTA DEL ENVIO DE INFORMACION
        $responseString = curl_exec($curl);
        $response = json_decode($responseString, true);
        $fecha = date("Y-m-d H:i:s");
        file_put_contents('responseWhatsapp.txt', $fecha . "-" . $responseString);
        //OBTENEMOS EL CODIGO DE LA RESPUESTA
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //CERRAMOS EL CURL
        curl_close($curl);
        global $textoCita;
        if (strpos($enviado, $textoCita) !== false) {
            $cita_creada = "1";
        } else {
            $cita_creada = "0";
        }
        try {
            //Cambiar el texto de la cita para que chatgpt no detecte que fue una cita
            $enviado = str_replace($textoCita, "Un asesor se pondrá en contacto con usted el día", $enviado);
            //INSERTAMOS LOS REGISTROS DEL ENVIO DEL WHATSAPP
            $sql = "INSERT INTO kimai2_registro 
        (mensaje_recibido, mensaje_enviado, id_wa, timestamp_wa, telefono_wa, cita_creada) 
        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssss', $recibido, $enviado, $idWA, $timestamp, $telefono, $cita_creada);
            $result = $stmt->execute();
            if ($result) {
                echo "El registro se insertó correctamente";
            } else {
                $error = $stmt->error;
                echo "Error al insertar el registro: " . $error;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            echo "Error al insertar el registro: " . $error;
        }
    }
}
