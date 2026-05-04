<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class CajasSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. Creamos la "Salsa Indie" (Evitamos duplicados con IGNORE)
        $this->execute("
            INSERT IGNORE INTO Caja (id_caja, nombre, precio, imagen) 
            VALUES (1, 'Salsa Indie', 150, 'bote_indie.png')
        ");

        // 2. Limpiamos las recompensas de la caja 1 por si cambiamos los porcentajes y metemos los nuevos
        $this->execute("DELETE FROM Recompensa_Caja WHERE id_caja = 1");
        
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, id_videojuego, puntos_premio, probabilidad) 
            VALUES 
            (1, 'puntos', NULL, 50, 60.00),
            (1, 'puntos', NULL, 200, 35.00),
            (1, 'juego', 10, 0, 5.00)
        ");

        // 3. Le damos dinero al usuario 1 para poder probar la tienda
        $this->execute("UPDATE Usuario SET puntos = 1000 WHERE id_usuario = 1");
    }
}