<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIdItemToRecompensa extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('Recompensa_Caja');
        // Solo añadimos la columna si no existe (para que no te pete a ti)
        if (!$table->hasColumn('id_item')) {
            $table->addColumn('id_item', 'integer', [
                'signed' => false, 
                'null' => true, 
                'after' => 'id_videojuego'
            ])->update();
        }
    }
}
