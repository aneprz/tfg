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
                'rating_medio'      => 9.40,
                'portada'           => 'media/portadaCyberpunk.jpg',
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
