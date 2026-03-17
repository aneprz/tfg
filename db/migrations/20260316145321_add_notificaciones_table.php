<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNotificacionesTable extends AbstractMigration
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
        $table = $this->table('Notificacion', ['id' => 'id_notificacion', 'signed' => false]);
        $table->addColumn('mensaje', 'text')
          ->addColumn('url_destino', 'string', ['limit' => 255, 'null' => true])
          ->addColumn('leida', 'boolean', ['default' => false])
          ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
          ->addColumn('tipo', 'enum', ['values' => ['usuario', 'comunidad', 'sistema'], 'default' => 'sistema'])
          ->addColumn('id_usuario_destino', 'integer', ['signed' => false]) 
          ->create();
    }
}
