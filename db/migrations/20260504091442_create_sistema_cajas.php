<?php
declare(strict_types=1);
use Phinx\Migration\AbstractMigration;

final class CreateSistemaCajas extends AbstractMigration
{
    public function change(): void
    {
        // 1. Añadimos la columna "puntos" a los Usuarios (si no la tienen ya)
        $tablaUsuario = $this->table('Usuario');
        if (!$tablaUsuario->hasColumn('puntos')) {
            $tablaUsuario->addColumn('puntos', 'integer', ['default' => 500, 'comment' => 'Saldo para comprar cajas'])
                         ->update();
        }

        // 2. Creamos la tabla de Cajas (El escaparate)
        $tablaCajas = $this->table('Caja', ['id' => 'id_caja']);
        $tablaCajas->addColumn('nombre', 'string', ['limit' => 100])
                   ->addColumn('precio', 'integer')
                   ->addColumn('imagen', 'string', ['limit' => 255])
                   ->create();

       // ... (parte de arriba del archivo igual)

        // 3. Creamos la tabla de Recompensas (Lo que hay dentro y su % de salir)
        $tablaRecompensas = $this->table('Recompensa_Caja', ['id' => 'id_recompensa']);
        $tablaRecompensas->addColumn('id_caja', 'integer', ['signed' => false]) // <-- CORRECCIÓN AQUÍ
                         ->addColumn('tipo_premio', 'string', ['limit' => 20])
                         ->addColumn('id_videojuego', 'integer', ['null' => true])
                         ->addColumn('puntos_premio', 'integer', ['default' => 0])
                         ->addColumn('probabilidad', 'decimal', ['precision' => 5, 'scale' => 2])
                         ->addForeignKey('id_caja', 'Caja', 'id_caja', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                         ->create();
    }
}