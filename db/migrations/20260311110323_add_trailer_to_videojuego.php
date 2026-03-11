<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTrailerToVideojuego extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Videojuego')
            ->addColumn('trailer_youtube_id', 'string', [
                'limit' => 50,
                'null' => true,
                'after' => 'portada'
            ])
            ->addIndex(['trailer_youtube_id'])
            ->update();
    }
}