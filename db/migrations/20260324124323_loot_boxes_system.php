<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LootboxesSystem extends AbstractMigration
{
    public function change(): void
    {
        /*
         * =========================
         * SOLO MODIFICAR TIPO EN Tienda_Items
         * =========================
         */
        if ($this->hasTable('Tienda_Items')) {

            // 1️⃣ Modificar ENUM tipo para incluir lootbox
            $this->execute("
                ALTER TABLE Tienda_Items 
                MODIFY tipo ENUM('avatar','marco','fondo','lootbox') NOT NULL
            ");

            // Ya no necesitamos lootbox_id ni FK
        }

        /*
         * =========================
         * TABLA RECOMPENSAS (lootbox_recompensas)
         * =========================
         */
        if (!$this->hasTable('lootbox_recompensas')) {
            $this->table('lootbox_recompensas')
                ->addColumn('id_lootbox', 'integer', ['signed' => false])
                ->addColumn('id_item', 'integer', ['signed' => false])
                ->addColumn('probabilidad', 'integer')
                ->addForeignKey('id_item', 'Tienda_Items', 'id_item', ['delete'=> 'CASCADE'])
                ->create();
        }
    }
}