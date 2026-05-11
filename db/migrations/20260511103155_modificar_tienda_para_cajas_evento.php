<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ModificarTiendaParaCajasEvento extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change(): void
    {
        $table = $this->table('Tienda_Items');

        // 1. Añadimos la columna para el color neón (si no existe ya)
        if (!$table->hasColumn('color_neon')) {
            $table->addColumn('color_neon', 'string', [
                'limit' => 10,
                'default' => '#00ffcc',
                'null' => false,
                'comment' => 'Color del brillo neón para las cajas de eventos temporales'
            ]);
        }

        // 2. Ampliamos la columna 'tipo' para que no corte palabras largas
        $table->changeColumn('tipo', 'string', [
            'limit' => 50,
            'null' => true, // Lo dejamos en true por seguridad para evitar conflictos con registros viejos
            'comment' => 'Ampliado a 50 caracteres para soportar lootbox y puntos'
        ]);

        $table->update();
    }
}