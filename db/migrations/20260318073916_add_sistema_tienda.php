<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSistemaTienda extends AbstractMigration
{
    public function change(): void
    {
        /* =========================
           TIENDA → ITEMS DISPONIBLES
           ========================= */

        $this->table('Tienda_Items', ['id' => 'id_item'])
            ->addColumn('nombre', 'string', [
                'limit' => 100,
                'null' => false
            ])
            ->addColumn('descripcion', 'string', [
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('tipo', 'enum', [
                'values' => ['avatar', 'marco', 'fondo', 'insignia'],
                'null' => false
            ])
            ->addColumn('precio', 'integer', [
                'null' => false
            ])
            ->addColumn('rareza', 'enum', [
                'values' => ['comun', 'raro', 'epico', 'legendario'],
                'default' => 'comun'
            ])
            ->addColumn('imagen', 'string', [
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('activo', 'boolean', [
                'default' => true
            ])
            ->addColumn('fecha_creacion', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addIndex(['tipo'])
            ->addIndex(['activo'])
            ->create();


        /* =========================
           USUARIO → ITEMS COMPRADOS
           ========================= */

        $this->table('Usuario_Items', ['id' => 'id_usuario_item'])
            ->addColumn('id_usuario', 'integer', [
                'signed' => false,
                'null' => false
            ])
            ->addColumn('id_item', 'integer', [
                'signed' => false,
                'null' => false
            ])
            ->addColumn('equipado', 'boolean', [
                'default' => false
            ])
            ->addColumn('fecha_compra', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', [
                'delete' => 'CASCADE'
            ])
            ->addForeignKey('id_item', 'Tienda_Items', 'id_item', [
                'delete' => 'CASCADE'
            ])
            ->addIndex(['id_usuario'])
            ->addIndex(['id_item'])
            ->addIndex(['equipado'])
            ->create();


        /* =========================
           OPCIONAL → EVITAR DUPLICADOS
           (un usuario no puede comprar
            el mismo item 2 veces)
           ========================= */

        $this->table('Usuario_Items')
            ->addIndex(['id_usuario', 'id_item'], [
                'unique' => true
            ])
            ->update();


        /* =========================
           MEJORA → MOVIMIENTOS (TIPOS)
           ========================= */

        $this->table('Movimientos_Puntos')
            ->changeColumn('tipo', 'enum', [
                'values' => ['logro', 'gasto', 'bonus', 'admin', 'compra'],
                'default' => 'logro'
            ])
            ->update();
    }
}