<?php

use Phinx\Migration\AbstractMigration;

class AddMovimientosTiposLootboxDuplicado extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('Movimientos_Puntos');

        $table->changeColumn('tipo', 'enum', [
            'values' => [
                'logro',
                'gasto',
                'bonus',
                'admin',
                'compra',
                'lootbox',
                'duplicado'
            ],
            'null' => false
        ])->update();
    }
}