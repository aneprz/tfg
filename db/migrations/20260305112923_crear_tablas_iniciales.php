<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablasIniciales extends AbstractMigration
{
    public function change(): void
    {

        // 1. Usuario
        $usuario = $this->table('Usuario', ['id' => 'id_usuario']);
        $usuario
            ->addColumn('gameTag', 'string', ['limit' => 255])
            ->addColumn('nombre_apellido', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('biografia', 'text', ['null' => true])
            ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('fecha_registro', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('admin', 'boolean', ['default' => 0])
            ->addIndex(['gameTag'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        // 2. Logros
        $this->table('Logros', ['id' => 'id_logro'])
            ->addColumn('nombre_logro', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('puntos_logro', 'integer', ['null' => true])
            ->create();

        // 3. Videojuego
        $this->table('Videojuego', ['id' => 'id_videojuego'])
            ->addColumn('titulo', 'string', ['limit' => 255])
            ->addColumn('descripcion', 'text', ['null' => true])
            ->addColumn('fecha_lanzamiento', 'date', ['null' => true])
            ->addColumn('developer', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('rating_medio', 'decimal', ['precision' => 3, 'scale' => 2, 'null' => true])
            ->addColumn('portada', 'string', ['limit' => 500, 'null' => true])
            ->create();

        // 4. Genero
        $this->table('Genero', ['id' => 'id_genero'])
            ->addColumn('nombre_genero', 'string', ['limit' => 255])
            ->create();

        // 5. Plataforma
        $this->table('Plataforma', ['id' => 'id_plataforma'])
            ->addColumn('nombre_plataforma', 'string', ['limit' => 255])
            ->create();

        // 6. Amigos
        $this->table('Amigos', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_amigo']
        ])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_amigo', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('fecha_amistad', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('estado', 'enum', ['values' => ['pendiente', 'aceptada'], 'default' => 'pendiente'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->addForeignKey('id_amigo', 'Usuario', 'id_usuario')
            ->create();

        // 7. Logros Usuario
        $this->table('Logros_Usuario', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_logro']
        ])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_logro', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('fecha_obtencion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->addForeignKey('id_logro', 'Logros', 'id_logro')
            ->create();

        // 8. Biblioteca
        $this->table('Biblioteca', [
            'id' => false,
            'primary_key' => ['id_usuario', 'id_videojuego']
        ])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('estado', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('horas_totales', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
            ->create();

        // 9. Resena
        $this->table('Resena', ['id' => 'id_resena'])
            ->addColumn('id_usuario', 'integer', ['signed' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false])
            ->addColumn('puntuacion', 'integer', ['null' => true])
            ->addColumn('texto_resena', 'text', ['null' => true])
            ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
            ->create();

        // 10. Multimedia
        $this->table('Multimedia', ['id' => 'id_media'])
            ->addColumn('id_usuario', 'integer', ['signed' => false])
            ->addColumn('id_videojuego', 'integer', ['signed' => false])
            ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('url_archivo', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('likes_count', 'integer', ['default' => 0])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego')
            ->create();

        // 11. Comunidad
        $this->table('Comunidad', ['id' => 'id_comunidad'])
            ->addColumn('nombre', 'string', ['limit' => 255])
            ->addColumn('id_videojuego_principal', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('id_creador', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('banner_url', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('id_videojuego_principal', 'Videojuego', 'id_videojuego')
            ->addForeignKey('id_creador', 'Usuario', 'id_usuario')
            ->create();

        // 12. Miembro Comunidad
        $this->table('Miembro_comunidad', [
            'id' => false,
            'primary_key' => ['id_comunidad', 'id_usuario']
        ])
            ->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('rol', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad')
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->create();

        // 13. Canal
        $this->table('Canal', ['id' => 'id_canal'])
            ->addColumn('id_comunidad', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('nombre_canal', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tipo', 'string', ['limit' => 50, 'null' => true])
            ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad')
            ->create();

        // 14. Mensaje
        $this->table('Mensaje', ['id' => 'id_mensaje'])
            ->addColumn('id_canal', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('contenido', 'text', ['null' => true])
            ->addColumn('fecha_envio', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_canal', 'Canal', 'id_canal')
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->create();

        // 15. Post
        $this->table('Post', ['id' => 'id_post'])
            ->addColumn('id_usuario', 'integer', ['signed' => false])
            ->addColumn('id_comunidad', 'integer', ['signed' => false])
            ->addColumn('contenido', 'text')
            ->addColumn('archivo_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tipo_archivo', 'enum', ['values' => ['texto', 'imagen', 'video'], 'default' => 'texto'])
            ->addColumn('fecha_publicacion', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', ['delete' => 'CASCADE'])
            ->addForeignKey('id_comunidad', 'Comunidad', 'id_comunidad', ['delete' => 'CASCADE'])
            ->create();

        // 16. Post Likes
        $this->table('Post_likes', [
            'id' => false,
            'primary_key' => ['id_post', 'id_usuario']
        ])
            ->addColumn('id_post', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => false])
            ->addForeignKey('id_post', 'Post', 'id_post', ['delete' => 'CASCADE'])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario', ['delete' => 'CASCADE'])
            ->create();

        // 17. Historico Stats
        $this->table('Historico_stats', ['id' => 'id_stat'])
            ->addColumn('id_usuario', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('mes', 'integer', ['null' => true])
            ->addColumn('anio', 'integer', ['null' => true])
            ->addColumn('genero_mas_jugado', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('total_horas_mes', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
            ->addForeignKey('id_usuario', 'Usuario', 'id_usuario')
            ->create();
    }
}