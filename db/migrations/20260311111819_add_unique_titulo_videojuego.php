<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueTituloVideojuego extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Videojuego')
            ->addIndex(['titulo'], ['unique' => true])
            ->update();
    }
}