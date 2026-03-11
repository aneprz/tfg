<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSteamIdToUsuario extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Usuario')
            ->addColumn('steamid', 'string', [
                'limit' => 30,
                'null' => true,
                'after' => 'id_usuario'
            ])
            ->addIndex(['steamid'])
            ->update();
    }
}