<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class CajasSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. CREAMOS LAS CAJAS
        $this->execute("
            INSERT IGNORE INTO Caja (id_caja, nombre, precio, imagen) 
            VALUES 
            (1, 'Salsa Indie', 150, 'bote_indie.png'),
            (2, 'Salsa Triple A', 500, 'bote_tripleA.png')
        ");

        // 2. CREAMOS LOS OBJETOS DE LA TIENDA (Los 3 de la Indie y los 5 de la Triple A)
        $this->execute("
            INSERT IGNORE INTO tienda_items (id_item, nombre, tipo, imagen, precio) 
            VALUES 
            (101, 'Jalapeño Cabreado', 'avatar', 'premiosIndie/1.png', 0),
            (102, 'Poción de Salsa', 'avatar', 'premiosIndie/2.png', 0),
            (103, 'Salsa Píxel', 'avatar', 'premiosIndie/3.png', 0),
            (201, 'Caballero de Fuego', 'avatar', 'premiosTripleA/1.png', 0),
            (202, 'Dragón de Chile', 'avatar', 'premiosTripleA/2.png', 0),
            (203, 'Cyborg Picante', 'avatar', 'premiosTripleA/3.png', 0),
            (204, 'Samurái Wasabi', 'avatar', 'premiosTripleA/4.png', 0),
            (205, 'Caldero Infernal', 'avatar', 'premiosTripleA/5.png', 0)
        ");

        // 3. LIMPIAMOS LAS RECOMPENSAS PARA QUE NO SE DUPLIQUEN SI EJECUTAMOS EL COMANDO 2 VECES
        $this->execute("DELETE FROM Recompensa_Caja WHERE id_caja IN (1, 2)");
        
        // 4. METEMOS LOS PREMIOS DE LA CAJA INDIE
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_item, puntos_premio, probabilidad) 
            VALUES 
            (1, 'puntos', 'Cashback Menor', 'logoPlatino.png', NULL, 50, 50.00),
            (1, 'puntos', 'Cashback Mayor', 'logoPlatino.png', NULL, 200, 30.00),
            (1, 'avatar', 'Jalapeño Cabreado', 'premiosIndie/1.png', 101, 0, 6.00),
            (1, 'avatar', 'Poción de Salsa', 'premiosIndie/2.png', 102, 0, 6.00),
            (1, 'avatar', 'Salsa Píxel', 'premiosIndie/3.png', 103, 0, 6.00),
            (1, 'puntos', '¡JACKPOT LEGENDARIO!', 'logoPlatino.png', NULL, 1200, 2.00)
        ");

        // 5. METEMOS LOS PREMIOS DE LA CAJA TRIPLE A
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_item, puntos_premio, probabilidad) 
            VALUES 
            (2, 'puntos', 'Reembolso Parcial', 'logoPlatino.png', NULL, 200, 50.00),
            (2, 'puntos', 'Ganancia Triple A', 'logoPlatino.png', NULL, 750, 30.00),
            (2, 'avatar', 'Caballero de Fuego', 'premiosTripleA/1.png', 201, 0, 3.60),
            (2, 'avatar', 'Dragón de Chile', 'premiosTripleA/2.png', 202, 0, 3.60),
            (2, 'avatar', 'Cyborg Picante', 'premiosTripleA/3.png', 203, 0, 3.60),
            (2, 'avatar', 'Samurái Wasabi', 'premiosTripleA/4.png', 204, 0, 3.60),
            (2, 'avatar', 'Caldero Infernal', 'premiosTripleA/5.png', 205, 0, 3.60),
            (2, 'puntos', '¡EL GORDO TRIPLE A!', 'logoPlatino.png', NULL, 3000, 2.00)
        ");

        // 6. LE DAMOS PASTA AL USUARIO DE PRUEBA PARA QUE PUEDA GASTAR
        $this->execute("UPDATE Usuario SET puntos = 5000 WHERE id_usuario = 1");
        // --- CAJA 3: SALSA GOTY ---
        
        // 1. Creamos la caja en la BD (si no la tienes ya creada)
        $this->execute("
            INSERT IGNORE INTO Caja (id_caja, nombre, precio, imagen) 
            VALUES (3, 'Salsa Goty', 1200, 'caja_legendaria.png')
        ");

        // 2. Metemos los 5 objetos Legendarios a la tienda
        $this->execute("
            INSERT IGNORE INTO tienda_items (id_item, nombre, tipo, imagen, precio) 
            VALUES 
            (301, 'Chile Cósmico', 'avatar', 'premiosGoty/1.png', 0),
            (302, 'Mando de Platino', 'avatar', 'premiosGoty/2.png', 0),
            (303, 'Corona Capsaicina', 'avatar', 'premiosGoty/3.png', 0),
            (304, 'Dios de la Salsa', 'avatar', 'premiosGoty/4.png', 0),
            (305, 'Cáliz del Jugador', 'avatar', 'premiosGoty/5.png', 0)
        ");

        // 3. Limpiamos y metemos las probabilidades de la Goty
        $this->execute("DELETE FROM Recompensa_Caja WHERE id_caja = 3");
        
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_item, puntos_premio, probabilidad) 
            VALUES 
            (3, 'puntos', 'Golpe Bajo', 'logoPlatino.png', NULL, 400, 50.00),
            (3, 'puntos', 'Inversión Rentable', 'logoPlatino.png', NULL, 1800, 30.00),
            (3, 'avatar', 'Chile Cósmico', 'premiosGoty/1.png', 301, 0, 3.60),
            (3, 'avatar', 'Mando de Platino', 'premiosGoty/2.png', 302, 0, 3.60),
            (3, 'avatar', 'Corona Capsaicina', 'premiosGoty/3.png', 303, 0, 3.60),
            (3, 'avatar', 'Dios de la Salsa', 'premiosGoty/4.png', 304, 0, 3.60),
            (3, 'avatar', 'Cáliz del Jugador', 'premiosGoty/5.png', 305, 0, 3.60),
            (3, 'puntos', '¡PELOTAZO HISTÓRICO!', 'logoPlatino.png', NULL, 10000, 2.00)
        ");
    }
}