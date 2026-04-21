<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMensajesNoLeidosToChatParticipante extends AbstractMigration
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
    public function change()
    {
        $table = $this->table('chat_participante');
        
        // Añadir columna mensajes_no_leidos con valor por defecto 0
        $table->addColumn('mensajes_no_leidos', 'integer', [
            'limit' => 11,
            'default' => 0,
            'null' => false,
            'comment' => 'Número de mensajes no leídos del usuario en esta conversación'
        ]);
        
        $table->update();
    }
}
