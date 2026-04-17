<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRememberMeTokens extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Remember_Token', ['id' => 'id_remember_token'])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('selector', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('token_hash', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('expires_at', 'datetime', ['null' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('last_used_at', 'datetime', ['null' => true])
            ->addIndex(['selector'], ['unique' => true])
            ->addIndex(['id_usuario'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}

