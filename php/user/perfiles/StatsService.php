<?php

require_once __DIR__ . '/../../../db/conexiones.php';

class StatsService {

    private static function db() {
        global $conexion;
        return $conexion;
    }

    public static function getUserStats($userId) {
        return [
            'horas_totales' => self::getHoras($userId),
            'juegos_totales' => self::getTotalJuegos($userId),
            'completados' => self::getCompletados($userId),
            'ratio_abandono' => self::getRatio($userId),
            'media_puntuacion' => self::getMediaPuntuacion($userId),
            'evolucion' => self::getEvolucion($userId),
            'top_juegos' => self::getTopJuegos($userId)
        ];
    }

    private static function getHoras($userId) {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(horas_totales),0) as total 
            FROM Biblioteca 
            WHERE id_usuario = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return (float)$stmt->get_result()->fetch_assoc()['total'];
    }

    private static function getTotalJuegos($userId) {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM Biblioteca 
            WHERE id_usuario = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    private static function getCompletados($userId) {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM Biblioteca 
            WHERE id_usuario = ? AND estado = 'completado'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    private static function getRatio($userId) {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT 
                SUM(CASE WHEN estado = 'abandonado' THEN 1 ELSE 0 END) as abandonados,
                SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados
            FROM Biblioteca 
            WHERE id_usuario = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_assoc();

        $ab = (int)$data['abandonados'];
        $co = (int)$data['completados'];

        $total = $ab + $co;

        return $total > 0 ? $ab / $total : 0;
    }

    /* ✅ YA DEVUELVE 0–10 DIRECTO */
    private static function getMediaPuntuacion($userId) {
        $db = self::db();

        $stmt = $db->prepare("
            SELECT COALESCE(AVG(puntuacion),0) as media 
            FROM Resena 
            WHERE id_usuario = ?
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return round((float)$stmt->get_result()->fetch_assoc()['media'], 2);
    }

    /* ✅ SIN DOBLE ESCALA */
    private static function getEvolucion($userId) {
        $db = self::db();

        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(fecha_publicacion, '%Y-%m') as mes,
                ROUND(AVG(puntuacion), 2) as media
            FROM Resena
            WHERE id_usuario = ?
            GROUP BY mes
            ORDER BY mes ASC
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'mes' => $row['mes'],
                'media' => (float)$row['media']
            ];
        }

        return $data;
    }

    private static function getTopJuegos($userId) {
        $db = self::db();

        $stmt = $db->prepare("
            SELECT v.titulo, b.horas_totales
            FROM Biblioteca b
            JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
            WHERE b.id_usuario = ?
            ORDER BY b.horas_totales DESC
            LIMIT 5
        ");

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }
}