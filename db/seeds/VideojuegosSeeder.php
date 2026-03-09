<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class VideojuegosSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // 1. Preparamos los datos en un array
        $datos = [
            [
                'titulo'            => 'The Legend of Zelda: Breath of the Wild',
                'descripcion'       => 'Una aventura épica en un mundo abierto lleno de secretos.',
                'fecha_lanzamiento' => '2017-03-03',
                'developer'         => 'Nintendo',
                'rating_medio'      => 9.75,
                'portada'           => 'media/portadaZeldaTOTK.jpg',
                'genero'            => 'Aventura',
                'plataforma'        => 'Switch'
            ],
            [
                'titulo'            => 'Elden Ring',
                'descripcion'       => 'Juego de rol y acción en un oscuro y vasto mundo de fantasía.',
                'fecha_lanzamiento' => '2022-02-25',
                'developer'         => 'FromSoftware',
                'rating_medio'      => 9.60,
                'portada'           => 'media/portadaEldenRing.jpg',
                'genero'            => 'RPG',
                'plataforma'        => 'PC'
            ],
            [
                'titulo'            => 'Hollow Knight',
                'descripcion'       => 'Un metroidvania clásico en 2D con un mundo interconectado.',
                'fecha_lanzamiento' => '2017-02-24',
                'developer'         => 'Team Cherry',
                'rating_medio'      => 9.10,
                'portada'           => 'media/portadaHollowKnight.jpg',
                'genero'            => 'Metroidvania',
                'plataforma'        => 'PC'
            ],
            [
                'titulo'            => 'Cyberpunk 2077',
                'descripcion'       => 'Cyberpunk 2077 es un RPG de aventura y acción de mundo abierto ambientado en la megalópolis de Night City, donde te pondrás en la piel de un mercenario o una mercenaria ciberpunk y vivirás su lucha a vida o muerte por la supervivencia.',
                'fecha_lanzamiento' => '2020-12-10',
                'developer'         => 'CD Projekt RED',
                'rating_medio'      => 8.00,
                'portada'           => 'media/portadaCyberpunk.jpg',
                'genero'            => 'Acción',
                'plataforma'        => 'PlayStation 4'
            ],
            [
                'titulo'            => 'Stardew Valley',
                'descripcion'       => 'Acabas de heredar la vieja granja que tu abuelo tenía en Stardew Valley, así que decides comenzar una nueva vida con la ayuda de unas cuantas herramientas de segunda mano y un puñado de monedas. ¿Podrás aprender a vivir de la tierra y convertir ese terreno tan descuidado en un hogar acogedor? No será fácil. Desde que Joja Corporation se instaló en el pueblo, sus habitantes han ido olvidando sus antiguas tradiciones. El centro cívico, uno de los núcleos de actividad más animados del pueblo antiguamente, ahora no es más que un montón de escombros. Sin embargo, el valle está lleno de posibilidades. ¡Con un poco de dedicación, tal vez puedas devolverle al valle todo su esplendor!',
                'fecha_lanzamiento' => '2016-02-26',
                'developer'         => 'ConcernedApe',
                'rating_medio'      => 9.40,
                'portada'           => 'media/portadaStardewValley.jpg',
                'genero'            => 'Simulación de granja',
                'plataforma'        => 'PlayStation 4'
            ],
            [
                'titulo'            => 'League of legends',
                'descripcion'       => 'League of Legends es un juego de estrategia por equipos en el que dos equipos conformados por cinco poderosos campeones se enfrentan para destruir la base del otro. Elige de entre más de 170 campeones para realizar jugadas épicas, asegurar asesinatos y destruir torretas mientras avanzas hacia la victoria.',
                'fecha_lanzamiento' => '2009-10-27',
                'developer'         => 'Riot Games',
                'rating_medio'      => 9.10,
                'portada'           => 'media/portadaLol.jpg',
                'genero'            => 'MOBA',
                'plataforma'        => 'PC'
            ],
            [
                'titulo'            => 'Watch Dogs',
                'descripcion'       => 'Watch_Dogs se desarrolla en una ciudad viva totalmente simulada. Gracias al smartphone de Aiden, tendrás control en tiempo real de la infraestructura de la ciudad. Atrapa a tu enemigo en una colisión en cadena de 30 coches manipulando los semáforos, para un tren y súbete a él para huir de las autoridades o levanta un puente para escapar de tus captores en el último momento. Cualquier cosa conectada al ctOS puede convertirse en tu arma.',
                'fecha_lanzamiento' => '2014-05-26',
                'developer'         => 'Ubisoft Montreal',
                'rating_medio'      => 8.10,
                'portada'           => 'media/portadaWatchdogs.jpg',
                'genero'            => 'Acción',
                'plataforma'        => 'PlayStation 4'
            ]
        ];

        // 2. Seleccionamos la tabla 'Videojuego'
        $tabla = $this->table('Videojuego');
        
        // 3. Insertamos los datos y guardamos
        $tabla->insert($datos)
              ->saveData();
    }
}
