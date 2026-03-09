<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablasIniciales extends AbstractMigration
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
        // 1. Usuarios
        $usuario = $this->table('Usuario', ['id' => 'id_usuario']);
        $usuario->addColumn('gameTag', 'string', ['limit' => 255])
                ->addColumn('nombre_apellido', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('email', 'string', ['limit' => 255])
                ->addColumn('password', 'string', ['limit' => 255])
                ->addColumn('biografia', 'text', ['null' => true])
                ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('fecha_registro', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['gameTag'], ['unique' => true])
                ->addIndex(['email'], ['unique' => true])
                ->create();

        // 2. Logros
        $logros = $this->table('Logros', ['id' => 'id_logro']);
        $logros->addColumn('nombre_logro', 'string', ['limit' => 255, 'null' => true])
               ->addColumn('descripcion', 'text', ['null' => true])
               ->addColumn('puntos_logro', 'integer', ['null' => true])
               ->create();

        // 3. Videojuegos, Géneros y Plataformas
        $videojuego = $this->table('Videojuego', ['id' => 'id_videojuego']);
        $videojuego->addColumn('titulo', 'string', ['limit' => 255])
                   ->addColumn('descripcion', 'text', ['null' => true])
                   ->addColumn('fecha_lanzamiento', 'date', ['null' => true])
                   ->addColumn('developer', 'string', ['limit' => 255, 'null' => true])
                   ->addColumn('rating_medio', 'decimal', ['precision' => 3, 'scale' => 2, 'null' => true])
                   ->addColumn('portada', 'string', ['limit' => 500, 'null' => true])
                   ->addColumn('genero', 'string', ['limit' => 20, 'null' => true])
                   ->addColumn('plataforma', 'string', ['limit' => 20, 'null' => true])
                   ->create();

        $genero = $this->table('Genero', ['id' => 'id_genero']);
        $genero->addColumn('nombre_genero', 'string', ['limit' => 255])
               ->create();

        $plataforma = $this->table('Plataforma', ['id' => 'id_plataforma']);
        $plataforma->addColumn('nombre_plataforma', 'string', ['limit' => 255])
                   ->create();

        // 4. Relaciones Sociales (Amigos)
        $amigos = $this->table('Amigos', ['id' => false, 'primary_key' => ['id_usuario', 'id_amigo']]);
        $amigos->addColumn('id_usuario', 'integer', ['signed' => false])
               ->addColumn('id_amigo', 'integer', ['signed' => false])
               ->addColumn('fecha_amistad', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
               ->addColumn('estado', 'enum', ['values' => ['pendiente', 'aceptada'], 'default' => 'pendiente'])
               ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
               ->addForeignKey('id_amigo', 'Usuario', 'id_usuario')
               ->create();

        // 5. Logros_Usuario
        $logrosUsuario = $this->table('Logros_Usuario', ['id' => false, 'primary_key' => ['id_usuario', 'id_logro']]);
        $logrosUsuario->addColumn('id_usuario', 'integer', ['signed' => false])
                      ->addColumn('id_logro', 'integer', ['signed' => false])
                      ->addColumn('fecha_obtencion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                      ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                      ->addForeignKey('id_logro', 'Logros', 'id_logro')
                      ->create();

        // 6. Biblioteca
        $biblioteca = $this->table('Biblioteca', ['id' => false, 'primary_key' => ['id_usuario', 'id_videojuego']]);
        $biblioteca->addColumn('id_usuario', 'integer', ['signed' => false])
                   ->addColumn('id_videojuego', 'integer', ['signed' => false])
                   ->addColumn('estado', 'string', ['limit' => 50, 'null' => true])
                   ->addColumn('horas_totales', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
                   ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                   ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
                   ->create();

        // 7. Resena
        $resena = $this->table('Resena', ['id' => 'id_resena']);
        $resena->addColumn('id_usuario', 'integer', ['signed' => false])
               ->addColumn('id_videojuego', 'integer', ['signed' => false])
               ->addColumn('puntuacion', 'integer', ['null' => true]) 
               ->addColumn('texto_resena', 'text', ['null' => true])
               ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
               ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
               ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
               ->create();

        // 8. Multimedia
        $multimedia = $this->table('Multimedia', ['id' => 'id_media']);
        $multimedia->addColumn('id_usuario', 'integer', ['signed' => false])
                   ->addColumn('id_videojuego', 'integer', ['signed' => false])
                   ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
                   ->addColumn('url_archivo', 'string', ['limit' => 255, 'null' => true])
                   ->addColumn('likes_count', 'integer', ['default' => 0])
                   ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                   ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
                   ->create();

        // 9. Comunidad
        $comunidad = $this->table('Comunidad', ['id' => 'id_comunidad']);
        $comunidad->addColumn('nombre', 'string', ['limit' => 255])
                  ->addColumn('id_videojuego_principal', 'integer', ['signed' => false, 'null' => true])
                  ->addColumn('id_creador', 'integer', ['signed' => false, 'null' => true])
                  ->addColumn('banner_url', 'string', ['limit' => 255, 'null' => true])
                  ->addForeignKey('id_videojuego_principal', 'Videojuego', 'id_videojuego')
                  ->addForeignKey('id_creador', 'Usuario', 'id_usuario')
                  ->create();

        // 10. Miembro_Comunidad
        $miembroComunidad = $this->table('Miembro_Comunidad', ['id' => false, 'primary_key' => ['id_comunidad', 'id_usuario']]);
        $miembroComunidad->addColumn('id_comunidad', 'integer', ['signed' => false])
                         ->addColumn('id_usuario', 'integer', ['signed' => false])
                         ->addColumn('rol', 'string', ['limit' => 50, 'null' => true])
                         ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad')
                         ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                         ->create();

        // 11. Canal
        $canal = $this->table('Canal', ['id' => 'id_canal']);
        $canal->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => true])
              ->addColumn('nombre_canal', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
              ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad')
              ->create();

        // 12. Mensaje
        $mensaje = $this->table('Mensaje', ['id' => 'id_mensaje']);
        $mensaje->addColumn('id_canal', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('contenido', 'text', ['null' => true])
                ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('id_canal', 'Canal', 'id_canal')
                ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                ->create();

        // 13. Post
        $post = $this->table('Post', ['id' => 'id_post']);
        $post->addColumn('id_usuario', 'integer', ['signed' => false])
             ->addColumn('id_comunidad', 'integer', ['signed' => false])
             ->addColumn('contenido', 'text')
             ->addColumn('archivo_url', 'string', ['limit' => 255, 'null' => true])
             ->addColumn('tipo_archivo', 'enum', ['values' => ['texto', 'imagen', 'video'], 'default' => 'texto'])
             ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', ['delete' => 'CASCADE'])
             ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad', ['delete' => 'CASCADE'])
             ->create();

        // 14. Post_Likes
        $postLikes = $this->table('Post_Likes', ['id' => false, 'primary_key' => ['id_post', 'id_usuario']]);
        $postLikes->addColumn('id_post', 'integer', ['signed' => false])
                  ->addColumn('id_usuario', 'integer', ['signed' => false])
                  ->addForeignKey('id_post', 'Post', 'id_post', ['delete' => 'CASCADE'])
                  ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', ['delete' => 'CASCADE'])
                  ->create();

        // 15. Historico_Stats
        $historico = $this->table('Historico_Stats', ['id' => 'id_stat']);
        $historico->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
                  ->addColumn('mes', 'integer', ['null' => true])
                  ->addColumn('anio', 'integer', ['null' => true])
                  ->addColumn('genero_mas_jugado', 'string', ['limit' => 100, 'null' => true])
                  ->addColumn('total_horas_mes', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
                  ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
                  ->create();
    }
}
