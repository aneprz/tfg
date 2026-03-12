<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIndexesPopularesSemana extends AbstractMigration
{
    public function change(): void
    {
        // Índices para acelerar la sección "Populares esta semana" (filtro por últimos 7 días).
        $this->table('Logros_Usuario')
            ->addIndex(['fecha_obtencion'])
            ->update();

        $this->table('Resena')
            ->addIndex(['fecha_publicacion', 'id_videojuego'])
            ->update();

        $this->table('Post')
            ->addIndex(['fecha_publicacion', 'id_comunidad'])
            ->update();
    }
}

