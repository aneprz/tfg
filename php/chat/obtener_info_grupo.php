<?php
// Activamos errores para ver qué pasa
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_GET['id_conv']) ? (int)$_GET['id_conv'] : 0;

// Crear array de respuesta
$data = [
    'miembros' => [],
    'amigos_fuera' => [],
    'soy_creador' => false,
    'id_creador' => 0,
    'nombre_grupo' => '',
    'foto_grupo' => ''
];

// Verificar que la conexión existe
if (!$conexion) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

if ($id_yo <= 0 || $id_conv <= 0) {
    echo json_encode(['error' => 'ID de usuario o conversación inválido', 'id_yo' => $id_yo, 'id_conv' => $id_conv]);
    exit;
}

// 1. Obtener información del grupo
$sqlGrupo = "SELECT id_usuario_creador, nombre_grupo, foto_grupo FROM chat_conversacion WHERE id_conversacion = $id_conv";
$resGrupo = mysqli_query($conexion, $sqlGrupo);

if (!$resGrupo) {
    echo json_encode(['error' => 'Error en consulta de grupo: ' . mysqli_error($conexion)]);
    exit;
}

$rowGrupo = mysqli_fetch_assoc($resGrupo);
if (!$rowGrupo) {
    echo json_encode(['error' => 'No se encontró el grupo con ID: ' . $id_conv]);
    exit;
}

$data['id_creador'] = (int)$rowGrupo['id_usuario_creador'];
$data['soy_creador'] = ($id_yo == $data['id_creador']);
$data['nombre_grupo'] = $rowGrupo['nombre_grupo'] ?? 'Grupo sin nombre';
$data['foto_grupo'] = $rowGrupo['foto_grupo'] ?? 'assets/img/grupos/grupo_default.png';

// 2. Obtener miembros del grupo
$sqlMiembros = "SELECT u.id_usuario, u.gameTag, u.avatar 
                FROM chat_participante p 
                INNER JOIN Usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.id_conversacion = $id_conv";
$resMiembros = mysqli_query($conexion, $sqlMiembros);

if (!$resMiembros) {
    echo json_encode(['error' => 'Error en consulta de miembros: ' . mysqli_error($conexion)]);
    exit;
}

while ($row = mysqli_fetch_assoc($resMiembros)) {
    $data['miembros'][] = [
        'id_usuario' => (int)$row['id_usuario'],
        'gameTag' => $row['gameTag'],
        'foto_perfil' => !empty($row['avatar']) ? "../../img/avatares/" . $row['avatar'] : "../../img/avatares/default.png",
        'es_creador' => ((int)$row['id_usuario'] == $data['id_creador'])
    ];
}

// 3. Obtener amigos que NO están en el grupo
$sqlAmigos = "SELECT DISTINCT u.id_usuario, u.gameTag, u.avatar 
              FROM amigos a 
              INNER JOIN Usuario u ON (
                  (a.id_usuario = $id_yo AND a.id_amigo = u.id_usuario) OR 
                  (a.id_amigo = $id_yo AND a.id_usuario = u.id_usuario)
              )
              WHERE u.id_usuario != $id_yo 
              AND a.estado = 'aceptada'
              AND u.id_usuario NOT IN (
                  SELECT id_usuario FROM chat_participante WHERE id_conversacion = $id_conv
              )";

$resAmigos = mysqli_query($conexion, $sqlAmigos);

if ($resAmigos) {
    while ($row = mysqli_fetch_assoc($resAmigos)) {
        $data['amigos_fuera'][] = [
            'id_usuario' => (int)$row['id_usuario'],
            'gameTag' => $row['gameTag'],
            'foto_perfil' => !empty($row['avatar']) ? "../../img/avatares/" . $row['avatar'] : "../../img/avatares/default.png"
        ];
    }
}

// Enviar respuesta
echo json_encode($data);
exit;
?>