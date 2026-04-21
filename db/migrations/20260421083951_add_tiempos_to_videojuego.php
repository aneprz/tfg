<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTiemposToVideojuego extends AbstractMigration
{
    public function change(): void
    {
        // Seleccionamos la tabla Videojuego
        $table = $this->table('Videojuego');

        // Añadimos las dos columnas de tiempo. 
        // Si ya existen (porque las creaste a mano antes), Phinx lo gestionará sin petar.
        $table->addColumn('tiempo_historia', 'integer', ['default' => 0, 'null' => false])
              ->addColumn('tiempo_completo', 'integer', ['default' => 0, 'null' => false])
              ->update();
    }
}