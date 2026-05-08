<?php
use Phinx\Migration\AbstractMigration;

class AddNombreImagenToRecompensa extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('Recompensa_Caja');
        
        // Añadimos las dos columnas que nos faltan si no existen
        if (!$table->hasColumn('nombre_premio')) {
            $table->addColumn('nombre_premio', 'string', [
                'limit' => 100, 
                'null' => true, 
                'after' => 'tipo_premio'
            ])
            ->addColumn('imagen_premio', 'string', [
                'limit' => 255, 
                'null' => true, 
                'after' => 'nombre_premio'
            ])
            ->update();
        }
    }
}