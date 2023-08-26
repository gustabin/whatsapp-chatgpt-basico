<?php
function creaCita($cliente, $telefono, $proyecto, $usuario, $descripcion)
{
    try {
        global $conn;
        $cliente = str_replace("Cliente: ", "", $cliente);

        // Obtén las horas de inicio y fin del negocio
        $sql = "SELECT value FROM kimai2_configuration WHERE name = 'calendar.businessHours.begin'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $businessHoursBegin = new DateTime($row['value']);

        $sql = "SELECT value FROM kimai2_configuration WHERE name = 'calendar.businessHours.end'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $businessHoursEnd = new DateTime($row['value']);

        // Verifica si el cliente ya existe
        $sql = "SELECT id FROM `kimai2_customers` WHERE `phone` = '$telefono'";
        $result = $conn->query($sql);
        $clienteRow = $result->fetch_assoc();

        if ($clienteRow) {
            // Si el cliente existe, toma el ID existente
            $client_id = $clienteRow['id'];
        } else {
            // Si el cliente no existe, crea uno nuevo
            $sql = "INSERT INTO `kimai2_customers` (`name`, `phone`, `visible`, `country`, `currency`, `timezone`) VALUES ('$cliente', '$telefono', '1', 'ES', 'ARS', 'Atlantic/St_Helena')";
            $conn->query($sql);
            $client_id = $conn->insert_id;  // Toma el ID del cliente recién creado
        }

        // Verifica si el proyecto ya existe para este cliente
        $sql = "SELECT id FROM `kimai2_projects` WHERE `customer_id` = $client_id LIMIT 1";
        $result = $conn->query($sql);
        $proyectoRow = $result->fetch_assoc();

        if ($proyectoRow) {
            // Si el proyecto existe, toma el ID existente
            $project_id = $proyectoRow['id'];
        } else {
            // Si el proyecto no existe, crea uno nuevo
            $sql = "INSERT INTO `kimai2_projects` (`customer_id`,`name`, `visible`) VALUES ('$client_id', '$proyecto', '1')";
            $conn->query($sql);
            $project_id = $conn->insert_id;  // Toma el ID del proyecto recién creado
        }

        // Determina la cantidad de días y minutos basados en el tipo de usuario
        $diasAdelanto = 0;
        $minutos = 0;
        switch ($usuario) {
            case 'Medico general':
                $diasAdelanto = 2;
                $minutos = 60;
                break;
            case 'Cardiologia':
                $diasAdelanto = 1;
                $minutos = 60;
                break;
            case 'Pediatria':
                $diasAdelanto = 1;
                $minutos = 60;
                break;
            default:
                $usuario = 'Medico general';
                $diasAdelanto = 2;
                $minutos = 60;
        }

        // Obtiene la fecha de hoy más el adelanto de días
        $fechaAdelantada = date('Y-m-d', strtotime("+$diasAdelanto days"));

        $start_time = $fechaAdelantada . ' ' . $businessHoursBegin->format('H:i:s');
        $segundos = $minutos * 60;  // Segundos de duración de la cita
        $end_time = date('Y-m-d H:i:s', strtotime($start_time) + $segundos);

        // Convertir a objetos DateTime para facilitar la comparación
        $startTime = new DateTime($start_time);
        $endTime = new DateTime($end_time);

        // Obtener el ID del usuario basado en el alias de usuario
        $sqlIdUser = "SELECT id FROM kimai2_users WHERE alias = '$usuario'";
        $resultIdUser = $conn->query($sqlIdUser);
        $user = $resultIdUser->fetch_assoc();
        $user_id = $user['id'];

        // Revisar cada intervalo de tiempo posible para la cita dentro del horario de trabajo
        while (true) {
            // Asegurarse que sea un día de la semana
            while ($startTime->format('N') >= 6) {  // Saltar sábados y domingos
                $startTime = getNextBusinessDay($startTime);
                $startTime->setTime($businessHoursBegin->format('H'), $businessHoursBegin->format('i'), $businessHoursBegin->format('s'));  // Configurar al inicio del horario de trabajo
                $endTime = clone $startTime;
                $endTime->modify('+' . $segundos . ' seconds');
            }

            // Comprobar si ya existe una cita para este usuario y este intervalo de tiempo
            $start_time = $startTime->format('Y-m-d H:i:s');
            $end_time = $endTime->format('Y-m-d H:i:s');

            // Si la cita termina después del horario de trabajo, configurar al inicio del próximo día hábil
            $businessHoursEnd->setDate($endTime->format('Y'), $endTime->format('m'), $endTime->format('d'));
            if ($endTime > $businessHoursEnd) {
                $startTime = getNextBusinessDay($startTime);
                $startTime->setTime($businessHoursBegin->format('H'), $businessHoursBegin->format('i'), $businessHoursBegin->format('s'));  // Configurar al inicio del horario de trabajo
                $endTime = clone $startTime;
                $endTime->modify('+' . $segundos . ' seconds');
                continue;  // Volver al inicio del ciclo
            }

            $sql = "SELECT * FROM `kimai2_timesheet` WHERE `user` = $user_id AND ((`start_time` >= '$start_time' AND `start_time` < '$end_time') OR (`end_time` > '$start_time' AND `end_time` <= '$end_time'))";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Si ya existe una cita en este intervalo de tiempo, mover el inicio y fin de la cita al próximo intervalo posible
                $startTime->modify("+{$minutos} minutes");
                $endTime->modify("+{$minutos} minutes");
            } else {
                // Si no hay ninguna cita en este intervalo de tiempo, es un intervalo válido para la cita
                break;
            }
        }

        // Obtener el ID del último registro con cita_creada=1
        $sql = "SELECT MAX(id) AS id FROM kimai2_registro WHERE cita_creada = 1";
        $resultCita = $conn->query($sql);
        $rowCita = $resultCita->fetch_assoc();
        $lastId = $rowCita['id'];

        // Si no se encontró ningún registro con cita_creada=1, seleccionar el primer registro
        if ($lastId === null) {
            $sql = "SELECT * FROM kimai2_registro ORDER BY id LIMIT 1";
        } else {
            // Seleccionar los registros a partir del registro con ID que es uno mayor que el último ID con cita_creada=1
            $sql = "SELECT * FROM kimai2_registro WHERE id > $lastId ORDER BY id";
        }
        $resultRegistro = $conn->query($sql);
        $descripcion = $descripcion . "\n CONVERSACION \n";
        // Procesar el resultado
        while ($rowRegistro = $resultRegistro->fetch_assoc()) {
            $descripcion = $descripcion . "Mensaje Recibido: {$rowRegistro['mensaje_recibido']}\n";
            $descripcion = $descripcion . "Mensaje Enviado: {$rowRegistro['mensaje_enviado']}\n";
            $descripcion = $descripcion . "-------------------------\n";
        }
        $descripcion = str_replace("'", "-", $descripcion);

        // // Crear un objeto DateTime a partir de la cadena de fecha
        // $date = new \DateTime($start_time);
        // // Sumarle 3 horas al objeto DateTime
        // $date->modify('+3 hours');
        // // Obtener la nueva fecha y hora
        // $new_start_time = $date->format('Y-m-d H:i:s');

        // // Crear un objeto DateTime a partir de la cadena de fecha
        // $date = new \DateTime($end_time);
        // // Sumarle 3 horas al objeto DateTime
        // $date->modify('+3 hours');
        // // Obtener la nueva fecha y hora
        // $new_end_time = $date->format('Y-m-d H:i:s');

        $sql = "INSERT INTO `kimai2_timesheet` 
            (`user`, `activity_id`, `project_id`, `start_time`, `end_time`, `duration`, `description`, `rate`, `hourly_rate`, `timezone`, `internal_rate`, `modified_at`, `date_tz`) VALUES 
            ('$user_id', '1', '$project_id', '$start_time', '$end_time', '$segundos', '" . $descripcion . "', '0', '0', 'Atlantic/St_Helena', '0', now(), now())";
        $conn->query($sql);

        reinicializar();

        //Limpiar la tabla registro
        $sql = "DELETE FROM kimai2_registro WHERE cita_creada = 0";
        $conn->query($sql);
        return $start_time;
    } catch (Exception $e) {
        echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        return null;
    }
}

function reinicializar()
{
    // Reinicializar las variables
    $cliente = "";
    $telefono = "";
    $proyecto = "";
    $usuario = "";
    $descripcion = "";
    $fechaCita = null;
    $textoCita = "";
}

function getNextBusinessDay($date)
{
    do {
        $date->modify('+1 day');
    } while ($date->format('N') >= 6);  // Saltar sábados y domingos
    return $date;
}
