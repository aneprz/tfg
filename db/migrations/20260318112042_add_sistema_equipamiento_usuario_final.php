<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSistemaEquipamientoUsuarioFinal extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('Usuario');

        /* =========================
           COLUMNAS EQUIPAMIENTO
           ========================= */

        $table->addColumn('avatar_activo', 'integer', [
            'null' => true,
            'signed' => false,
            'after' => 'puntos_actuales'
        ]);

        $table->addColumn('marco_activo', 'integer', [
            'null' => true,
            'signed' => false,
            'after' => 'avatar_activo'
        ]);

        $table->addColumn('fondo_activo', 'integer', [
            'null' => true,
            'signed' => false,
            'after' => 'marco_activo'
        ]);

        $table->addColumn('insignia_activa', 'integer', [
            'null' => true,
            'signed' => false,
            'after' => 'fondo_activo'
        ]);

        /* =========================
           ÍNDICES (IMPORTANTE)
           ========================= */

        $table->addIndex(['avatar_activo']);
        $table->addIndex(['marco_activo']);
        $table->addIndex(['fondo_activo']);
        $table->addIndex(['insignia_activa']);

        /* =========================
           FOREIGN KEYS
           ========================= */

        $table->addForeignKey('avatar_activo', 'Tienda_Items', 'id_item', [
            'delete' => 'SET_NULL',
            'update' => 'NO_ACTION'
        ]);

        $table->addForeignKey('marco_activo', 'Tienda_Items', 'id_item', [
            'delete' => 'SET_NULL',
            'update' => 'NO_ACTION'
        ]);

        $table->addForeignKey('fondo_activo', 'Tienda_Items', 'id_item', [
            'delete' => 'SET_NULL',
            'update' => 'NO_ACTION'
        ]);

        $table->addForeignKey('insignia_activa', 'Tienda_Items', 'id_item', [
            'delete' => 'SET_NULL',
            'update' => 'NO_ACTION'
        ]);

        $table->update();
    }
}