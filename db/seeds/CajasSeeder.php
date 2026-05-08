<?php
use Phinx\Seed\AbstractSeed;

class CajasSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1. CREAMOS TODAS LAS CAJAS
        $this->execute("
            INSERT IGNORE INTO Caja (id_caja, nombre, precio, imagen) 
            VALUES 
            (1, 'Salsa Indie', 150, 'bote_indie.png'),
            (2, 'Salsa Triple A', 500, 'bote_tripleA.png'),
            (3, 'Salsa Goty', 1200, 'caja_legendaria.png'),
            (4, 'Salsa Enmarcada', 300, 'caja_marcos.png')
        ");

        // 2. CREAMOS LOS AVATARES MANUALES DE LA TIENDA (Indie, Triple A, Goty)
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
            (205, 'Caldero Infernal', 'avatar', 'premiosTripleA/5.png', 0),
            (301, 'Chile Cósmico', 'avatar', 'premiosGoty/1.png', 0),
            (302, 'Mando de Platino', 'avatar', 'premiosGoty/2.png', 0),
            (303, 'Corona Capsaicina', 'avatar', 'premiosGoty/3.png', 0),
            (304, 'Dios de la Salsa', 'avatar', 'premiosGoty/4.png', 0),
            (305, 'Cáliz del Jugador', 'avatar', 'premiosGoty/5.png', 0)
        ");

        // 3. LIMPIAMOS LAS RECOMPENSAS PARA QUE NO SE DUPLIQUEN SI TIRAS EL SEEDER 2 VECES
        $this->execute("DELETE FROM Recompensa_Caja WHERE id_caja IN (1, 2, 3, 4)");
        
        // 4. METEMOS LOS PREMIOS DE LAS CAJAS 1, 2 Y 3
        $this->execute("
            INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_item, puntos_premio, probabilidad) 
            VALUES 
            (1, 'puntos', 'Cashback Menor', 'logoPlatino.png', NULL, 50, 50.00),
            (1, 'puntos', 'Cashback Mayor', 'logoPlatino.png', NULL, 200, 30.00),
            (1, 'avatar', 'Jalapeño Cabreado', 'premiosIndie/1.png', 101, 0, 6.00),
            (1, 'avatar', 'Poción de Salsa', 'premiosIndie/2.png', 102, 0, 6.00),
            (1, 'avatar', 'Salsa Píxel', 'premiosIndie/3.png', 103, 0, 6.00),
            (1, 'puntos', '¡JACKPOT LEGENDARIO!', 'logoPlatino.png', NULL, 1200, 2.00),

            (2, 'puntos', 'Reembolso Parcial', 'logoPlatino.png', NULL, 200, 50.00),
            (2, 'puntos', 'Ganancia Triple A', 'logoPlatino.png', NULL, 750, 30.00),
            (2, 'avatar', 'Caballero de Fuego', 'premiosTripleA/1.png', 201, 0, 3.60),
            (2, 'avatar', 'Dragón de Chile', 'premiosTripleA/2.png', 202, 0, 3.60),
            (2, 'avatar', 'Cyborg Picante', 'premiosTripleA/3.png', 203, 0, 3.60),
            (2, 'avatar', 'Samurái Wasabi', 'premiosTripleA/4.png', 204, 0, 3.60),
            (2, 'avatar', 'Caldero Infernal', 'premiosTripleA/5.png', 205, 0, 3.60),
            (2, 'puntos', '¡EL GORDO TRIPLE A!', 'logoPlatino.png', NULL, 3000, 2.00),

            (3, 'puntos', 'Golpe Bajo', 'logoPlatino.png', NULL, 400, 50.00),
            (3, 'puntos', 'Inversión Rentable', 'logoPlatino.png', NULL, 1800, 30.00),
            (3, 'avatar', 'Chile Cósmico', 'premiosGoty/1.png', 301, 0, 3.60),
            (3, 'avatar', 'Mando de Platino', 'premiosGoty/2.png', 302, 0, 3.60),
            (3, 'avatar', 'Corona Capsaicina', 'premiosGoty/3.png', 303, 0, 3.60),
            (3, 'avatar', 'Dios de la Salsa', 'premiosGoty/4.png', 304, 0, 3.60),
            (3, 'avatar', 'Cáliz del Jugador', 'premiosGoty/5.png', 305, 0, 3.60),
            (3, 'puntos', '¡PELOTAZO HISTÓRICO!', 'logoPlatino.png', NULL, 10000, 2.00)
        ");

        // 5. BUCLE MÁGICO ADAPTADO A TUS NOMBRES CON HASH
        // Le indicamos al seeder dónde está la carpeta físicamente
        $directorioMarcos = __DIR__ . '/../../media/marcos/';
        
        // Leemos el contenido de la carpeta y quitamos los invisibles '.' y '..'
        $archivos = array_values(array_diff(scandir($directorioMarcos), ['.', '..']));
        $totalMarcos = count($archivos);
        
        if ($totalMarcos === 0) {
            echo "¡ERROR! No he encontrado ninguna imagen en $directorioMarcos\n";
        } else {
            // Repartimos el 100% de probabilidad entre los que haya (54, 55, los que sean)
            $probabilidadMarco = round(100 / $totalMarcos, 2);
            $tiendaMarcos = [];
            $recompensaMarcos = [];

            foreach ($archivos as $index => $archivo) {
                $idItem = 401 + $index; // 401, 402, 403...
                
                // MAGIA: Limpiamos el nombre. 
                // Convierte "69c2b87ea5737_marco_hielo.png" en "Marco Hielo"
                $nombreLimpio = preg_replace('/^[a-f0-9]+_/i', '', $archivo); // Quita el código inicial
                $nombreLimpio = str_replace(['.png', '_'], ['', ' '], $nombreLimpio); // Quita extensión y guiones bajos
                $nombreLimpio = ucwords(trim($nombreLimpio)); // Pone mayúsculas "Marco Hielo"

                $imagen = 'marcos/' . $archivo; // "marcos/69c2b87ea5737_marco_hielo.png"

                $tiendaMarcos[] = "($idItem, '$nombreLimpio', 'marco', '$imagen', 0)";
                $recompensaMarcos[] = "(4, 'marco', '$nombreLimpio', '$imagen', $idItem, 0, $probabilidadMarco)";
            }

            // Borramos lo viejo e insertamos lo nuevo
            $this->execute("DELETE FROM tienda_items WHERE id_item > 400");
            $this->execute("INSERT IGNORE INTO tienda_items (id_item, nombre, tipo, imagen, precio) VALUES " . implode(', ', $tiendaMarcos));
            $this->execute("INSERT INTO Recompensa_Caja (id_caja, tipo_premio, nombre_premio, imagen_premio, id_item, puntos_premio, probabilidad) VALUES " . implode(', ', $recompensaMarcos));
        }
        // 6. PASTA PARA EL USUARIO PARA PODER HACER PRUEBAS SIN ARRUINARSE
        $this->execute("UPDATE Usuario SET puntos = 20000 WHERE id_usuario = 1");
    }
}