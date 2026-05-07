<?php
use Phinx\Migration\AbstractMigration;

class TablasYCajasSistema extends AbstractMigration
{
    public function change(): void
    {
        // 1. Tabla de Cajas (Si no existe)
        $tableCaja = $this->table('Caja', ['id' => false, 'primary_key' => 'id_caja']);
        if (!$tableCaja->exists()) {
            $tableCaja->addColumn('id_caja', 'integer', ['identity' => true, 'signed' => false])
                      ->addColumn('nombre', 'string', ['limit' => 100])
                      ->addColumn('precio', 'integer')
                      ->addColumn('imagen', 'string', ['limit' => 255])
                      ->create();
        }

        // 2. Tabla de Recompensas (Añadimos la columna id_item que creamos a mano)
        $tableRecompensa = $this->table('Recompensa_Caja');
        if ($tableRecompensa->exists()) {
            if (!$tableRecompensa->hasColumn('id_item')) {
                $tableRecompensa->addColumn('id_item', 'integer', [
                    'signed' => false, 
                    'null' => true, 
                    'after' => 'id_videojuego'
                ])->update();
            }
        }

        // 3. Tabla de Inventario de Usuario (usuario_items)
        $tableItems = $this->table('usuario_items', ['id' => false, 'primary_key' => 'id_usuario_item']);
        if (!$tableItems->exists()) {
            $tableItems->addColumn('id_usuario_item', 'integer', ['identity' => true, 'signed' => false])
                       ->addColumn('id_usuario', 'integer', ['signed' => false])
                       ->addColumn('id_item', 'integer', ['signed' => false])
                       ->addColumn('equipado', 'boolean', ['default' => 0])
                       ->addColumn('fecha_compra', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                       ->create();
        }
    }
}