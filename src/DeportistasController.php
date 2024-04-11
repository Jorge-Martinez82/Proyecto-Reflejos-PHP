<?php

namespace Jorgem\ProyectoReflejos;
require_once 'config.php';
use Symfony\Component\HttpClient\HttpClient;
class DeportistasController {

    public function getDeportistas() {
        $url = FIRESTORE_URL;
        $key = FIRESTORE_API_KEY;
        // Crear una instancia del cliente HTTP
        $client = HttpClient::create();

        // Realizar una solicitud GET a una URL
        $response = $client->request('GET', $url . 'deportistas' . $key);
        // Obtener el contenido de la respuesta en formato JSON
        $data = $response->toArray();


        $html = ''; // Inicializa la variable HTML fuera del bucle
        $html .= "<table class='table mt-2'>"; // Inicia la tabla

        foreach ($data['documents'] as $deportista) {
            $jsonDeportista = json_encode($deportista);
            $parts = explode('/', $deportista['name']);
            $deportistaId = end($parts);
            // Agrega una fila para cada campo del deportista
            $html .= "<tr><td><strong>Nombre:</strong></td><td>" . $deportista['fields']['nombre']['stringValue'] . "</td></tr>";
            $html .= "<tr><td><strong>Apellido:</strong></td><td>" . $deportista['fields']['apellido1']['stringValue'] . " " . $deportista['fields']['apellido2']['stringValue'] . "</td></tr>";
            $html .= "<tr><td><strong>Fecha de nacimiento:</strong></td><td>" . $deportista['fields']['fechanacimiento']['timestampValue'] . "</td></tr>";
            $html .= "<tr><td><strong>Deporte:</strong></td><td>" . $deportista['fields']['deporte']['stringValue'] . "</td></tr>";
            $html .= "<tr><td><strong>Club:</strong></td><td>" . $deportista['fields']['club']['stringValue'] . "</td></tr>";
            $html .= "<tr><td></td><td>
                <div class='d-flex justify-content-end'>
                    <form method='POST' class='mr-2'>
                        <input type='hidden' name='deportistaId' value='{$deportistaId}'>
                        <button class='btn btn-outline-danger btn-sm' type='submit' onclick=\"return confirm('¿Borrar Deportista?')\">Eliminar</button>
                    </form>
                    <form method='POST' action='modificarDeportista.php'>
                        <input type='hidden' name='deportista' value='".$jsonDeportista."'>
                        <button class='btn btn-outline-warning btn-sm' type='submit'>Modificar</button>
                    </form>
                </div>
              </td></tr>";

            // Agrega más filas aquí si es necesario
        }

        $html .= "</table>";
        return $html;

    }
    // Función para crear un nuevo usuario
    public function createDeportista($userData) {
        $url = FIRESTORE_PATH_CD;
        $key = FIRESTORE_API_KEY;

        // Crear una instancia del cliente HTTP
        $client = HttpClient::create();
        // Construir los datos del nuevo deportista
        $deportistaData = [
            'fields' => [
                'nombre' => ['stringValue' => $userData['nombre']],
                'apellido1' => ['stringValue' => $userData['apellido1']],
                'apellido2' => ['stringValue' => $userData['apellido2'] ?? ''],
                'fechanacimiento' => ['timestampValue' => $userData['fechanacimiento']],
                'deporte' => ['stringValue' => $userData['deporte']],
                'club' => ['stringValue' => $userData['club'] ?? ''],
            ]
        ];
        $jsonData = json_encode($deportistaData, JSON_UNESCAPED_UNICODE);

        // Realizar una solicitud POST a la URL de Firestore para agregar un nuevo documento a la colección de deportistas
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $jsonData,
        ]);

        // Verificar el código de respuesta
        if ($response->getStatusCode() === 200) {
            // El deportista se creó exitosamente
            return true;
        } else {
            // Ocurrió un error al crear el deportista
            return false;
        }
    }

    // Función para obtener un usuario por su ID
    public function getUser($userId) {
        // Implementa la lógica para obtener un usuario de la base de datos por su ID
    }

    // Función para actualizar la información de un usuario
    public function updateDeportista($userId, $newUserData) {
        // Implementa la lógica para actualizar la información de un usuario en la base de datos

        $updateMaskFields = '';

        foreach ($newUserData as $key => $value) {
            $updateMaskFields .= "&updateMask.fieldPaths={$key}";
        }

        $jsonArray = [
            'fields' => []
        ];

        foreach ($newUserData as $key => $value) {
            if ($key === 'fechanacimiento') {
                $jsonArray['fields'][$key] = [
                    'timestampValue' => $value,
                ];
            } else {
                $jsonArray['fields'][$key] = [
                    'stringValue' => $value,
                ];
            }
        }

        $json = json_encode($jsonArray, JSON_PRETTY_PRINT);

        $url = FIRESTORE_PATH_CD;
        $key = FIRESTORE_API_KEY;
        $urlfinal = $url . $userId . '?currentDocument.exists=true' . $updateMaskFields;
        $client = HttpClient::create();

        $response = $client->request('PATCH', $urlfinal, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $json,
        ]);
    }

    // Función para eliminar un usuario por su ID
    public function deleteDeportista($userId) {
        $url = FIRESTORE_PATH_CD;
        $key = FIRESTORE_API_KEY;
        $client = HttpClient::create();

        // Realizar una solicitud DELETE a la URL de Firestore para eliminar el deportista
        $response = $client->request('DELETE', $url . $userId . $key);

        // Verificar si la operación fue exitosa
        if ($response->getStatusCode() === 204) {
            return true; // Éxito
        } else {
            return false; // Error
        }
    }
}
