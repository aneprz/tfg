<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class CajasSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. Creamos la caja
        $this->execute("
            INSERT IGNORE INTO Caja (id_caja, nombre, precio, imagen) 
            VALUES (1, 'Salsa Indie', 150, 'bote_indie.png')
        ");

        // 2. IMPORTANTE: Creamos los objetos en la tienda para que tengan un ID real.
        // Les pongo IDs 101, 102 y 103 para no pisar si ya tenías otros.
        // OJO: Asumo que tu tabla tienda_items tiene estas columnas. Si se llaman distinto, dímelo.
        $this->execute("
            INSERT IGNORE INTO tienda_items (id_item, nombre, tipo, imagen, precio) 
            VALUES 
            (101, 'Jalapeño Cabreado', 'avatar', 'premiosIndie/1.png', 0),
            (102, 'Poción de Salsa', 'avatar', 'premiosIndie/2.png', 0),
            (103, 'Salsa Píxel', 'avatar', 'premiosIndie/3.png', 0)
        ");

        // 3. Limpiamos las recompensas de la caja 1 por si acaso
        $this->execute("DELETE FROM Recompensa_Caja WHERE id_caja = 1");
        
        // 4. Metemos las probabilidades con los IDs correctos (id_item) y las rutas de imagen
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_videojuego, id_item, puntos_premio, probabilidad) 
            VALUES 
            (1, 'puntos', 'Cashback Menor', 'logoPlatino.png', NULL, NULL, 50, 50.00),
            (1, 'puntos', 'Cashback Mayor', 'logoPlatino.png', NULL, NULL, 200, 30.00),
            (1, 'avatar', 'Jalapeño Cabreado', 'premiosIndie/1.png', NULL, 101, 0, 6.00),
            (1, 'avatar', 'Poción de Salsa', 'premiosIndie/2.png', NULL, 102, 0, 6.00),
            (1, 'avatar', 'Salsa Píxel', 'premiosIndie/3.png', NULL, 103, 0, 6.00),
            (1, 'puntos', '¡JACKPOT LEGENDARIO!', 'logoPlatino.png', NULL, NULL, 1200, 2.00)
        ");

        // 5. Le damos saldo al usuario para probar
        $this->execute("UPDATE Usuario SET puntos = 1000 WHERE id_usuario = 1");
    }
}