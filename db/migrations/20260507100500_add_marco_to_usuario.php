<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMarcoToUsuario extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('Usuario');
        if (!$table->hasColumn('marco_avatar')) {
            $table->addColumn('marco_avatar', 'string', [
                'limit' => 255, 
                'null' => true, 
                'after' => 'avatar'
            ])->update();
        }
    }
}
