<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablasIniciales extends AbstractMigration
{
    public function change(): void
    {

        // 1. Usuario
        $usuario = $this->table('usuario', ['id' => 'id_usuario']);
        $usuario
            ->addColumn('gameTag', 'string', ['limit' => 255])
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
        $logros = $this->table('logros', ['id' => 'id_logro']);
        $logros
            ->addColumn('nombre_logro', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('puntos_logro', 'integer', ['null' => true])
            ->create();


        // 3. Videojuego
        $videojuego = $this->table('videojuego', ['id' => 'id_videojuego']);
        $videojuego
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('fecha_lanzamiento', 'date', ['null' => true])
            ->addColumn('developer', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('rating_medio', 'decimal', ['precision' => 3, 'scale' => 2, 'null' => true])
            ->addColumn('portada', 'string', ['limit' => 500, 'null' => true])
            ->create();


        // 4. Genero
        $genero = $this->table('genero', ['id' => 'id_genero']);
        $genero
            ->addColumn('nombre_genero', 'string', ['limit' => 255])
            ->create();


        // 5. Plataforma
        $plataforma = $this->table('plataforma', ['id' => 'id_plataforma']);
        $plataforma
            ->addColumn('nombre_plataforma', 'string', ['limit' => 255])
            ->create();


        // 6. Amigos
        $amigos = $this->table('amigos', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_amigo']
        ]);

        $amigos
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_amigo', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('fecha_amistad', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('estado', 'enum', ['values' => ['pendiente', 'aceptada'], 'default' => 'pendiente'])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->addForeignKey('id_amigo', 'usuario', 'id_usuario')
            ->create();


        // 7. Logros Usuario
        $logrosUsuario = $this->table('logros_usuario', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_logro']
        ]);

        $logrosUsuario
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_logro', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('fecha_obtencion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->addForeignKey('id_logro', 'logros', 'id_logro')
            ->create();


        // 8. Biblioteca
        $biblioteca = $this->table('biblioteca', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_videojuego']
        ]);

        $biblioteca
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('estado', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('horas_totales', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'videojuego', 'id_videojuego')
            ->create();


        // 9. Resena
        $resena = $this->table('resena', ['id' => 'id_resena']);
        $resena
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('puntuacion', 'integer', ['null' => true])
            ->addColumn('texto_resena', 'text', ['null' => true])
            ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'videojuego', 'id_videojuego')
            ->create();


        // 10. Multimedia
        $multimedia = $this->table('multimedia', ['id' => 'id_media']);
        $multimedia
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('url_archivo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('likes_count', 'integer', ['default' => 0])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'videojuego', 'id_videojuego')
            ->create();


        // 11. Comunidad
        $comunidad = $this->table('comunidad', ['id' => 'id_comunidad']);
        $comunidad
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('id_videojuego_principal', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('id_creador', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('banner_url', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('id_videojuego_principal', 'videojuego', 'id_videojuego')
            ->addForeignKey('id_creador', 'usuario', 'id_usuario')
            ->create();


        // 12. Miembro Comunidad
        $miembroComunidad = $this->table('miembro_comunidad', [
            'id' => false,
            'primary_key' => ['id_comunidad', 'id_usuario']
        ]);

        $miembroComunidad
            ->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('rol', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('id_comunidad', 'comunidad', 'id_comunidad')
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->create();


        // 13. Canal
        $canal = $this->table('canal', ['id' => 'id_canal']);
        $canal
            ->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('nombre_canal', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('id_comunidad', 'comunidad', 'id_comunidad')
            ->create();


        // 14. Mensaje
        $mensaje = $this->table('mensaje', ['id' => 'id_mensaje']);
        $mensaje
            ->addColumn('id_canal', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('contenido', 'text', ['null' => true])
            ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_canal', 'canal', 'id_canal')
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->create();


        // 15. Post
        $post = $this->table('post', ['id' => 'id_post']);
        $post
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('contenido', 'text')
            ->addColumn('archivo_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tipo_archivo', 'enum', ['values' => ['texto', 'imagen', 'video'], 'default' => 'texto'])
            ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario', ['delete' => 'CASCADE'])
            ->addForeignKey('id_comunidad', 'comunidad', 'id_comunidad', ['delete' => 'CASCADE'])
            ->create();


        // 16. Post Likes
        $postLikes = $this->table('post_likes', [
            'id' => false,
            'primary_key' => ['id_post', 'id_usuario']
        ]);

        $postLikes
            ->addColumn('id_post', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addForeignKey('id_post', 'post', 'id_post', ['delete' => 'CASCADE'])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario', ['delete' => 'CASCADE'])
            ->create();


        // 17. Historico Stats
        $historico = $this->table('historico_stats', ['id' => 'id_stat']);
        $historico
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('mes', 'integer', ['null' => true])
            ->addColumn('anio', 'integer', ['null' => true])
            ->addColumn('genero_mas_jugado', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('total_horas_mes', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addForeignKey('id_usuario', 'usuario', 'id_usuario')
            ->create();
    }
}
