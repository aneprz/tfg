<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSistemaWalletPuntos extends AbstractMigration
{
    public function change(): void
    {
        /* =========================
           USUARIO → puntos actuales
           ========================= */

        $this->table('Usuario')
            ->addColumn('puntos_actuales', 'integer', [
                'default' => 0,
                'after' => 'admin'
            ])
            ->update();


        /* =========================
           MOVIMIENTOS DE PUNTOS
           ========================= */

        $this->table('Movimientos_Puntos', ['id' => 'id_movimiento'])
            ->addColumn('id_usuario', 'integer', [
                'signed' => false,
                'null' => false
            ])
            ->addColumn('puntos', 'integer', [
                'null' => false // puede ser positivo o negativo
            ])
            ->addColumn('tipo', 'enum', [
                'values' => ['logro', 'gasto', 'bonus', 'admin'],
                'default' => 'logro'
            ])
            ->addColumn('descripcion', 'string', [
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('fecha', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP'
            ])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', [
                'delete'=> 'CASCADE'
            ])
            ->addIndex(['id_usuario'])
            ->addIndex(['tipo'])
            ->create();
    }
}