<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearSistemaDeChat extends AbstractMigration
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
        // 1. Tabla de Conversaciones
        $tablaConversacion = $this->table('chat_conversacion', ['id' => 'id_conversacion']);
        $tablaConversacion->addColumn('tipo', 'enum', ['values' => ['individual', 'grupal'], 'default' => 'individual'])
                          ->addColumn('nombre_grupo', 'string', ['limit' => 255, 'null' => true])
                          ->addColumn('fecha_creacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                          ->create();

        // 2. Tabla de Participantes (CORREGIDA)
        $tablaParticipante = $this->table('chat_participante', ['id' => false, 'primary_key' => ['id_conversacion', 'id_usuario']]);
        $tablaParticipante->addColumn('id_conversacion', 'integer', ['signed' => false, 'null' => false]) // 'null' => false es la clave
                          ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])      // 'null' => false es la clave
                          ->addColumn('estado_solicitud', 'enum', ['values' => ['aceptada', 'pendiente'], 'default' => 'aceptada'])
                          ->addColumn('ultima_lectura', 'timestamp', ['null' => true])
                          ->addForeignKey('id_conversacion', 'chat_conversacion', 'id_conversacion', ['delete'=> 'CASCADE'])
                          ->addForeignKey('id_usuario', 'usuario', 'id_usuario', ['delete'=> 'CASCADE'])
                          ->create();

        // 3. Tabla de Mensajes
        $tablaMensaje = $this->table('chat_mensaje', ['id' => 'id_mensaje']);
        $tablaMensaje->addColumn('id_conversacion', 'integer', ['signed' => false, 'null' => false])
                     ->addColumn('id_emisor', 'integer', ['signed' => false, 'null' => false])
                     ->addColumn('contenido', 'text')
                     ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                     ->addForeignKey('id_conversacion', 'chat_conversacion', 'id_conversacion', ['delete'=> 'CASCADE'])
                     ->addForeignKey('id_emisor', 'usuario', 'id_usuario', ['delete'=> 'CASCADE'])
                     ->create();
    }
}
