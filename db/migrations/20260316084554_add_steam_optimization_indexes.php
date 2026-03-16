<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSteamOptimizationIndexes extends AbstractMigration
{
    public function change(): void
    {

        /* =========================
           INDEX VIDEOJUEGO
           ========================= */

        $table = $this->table('Videojuego');

        $table->addIndex(
            ['steam_appid'],
            ['name' => 'idx_steam_appid']
        )->update();


        /* =========================
           INDEX LOGROS
           ========================= */

        $table = $this->table('Logros');

        $table->addIndex(
            ['steam_api_name','id_videojuego'],
            ['name' => 'idx_logros_api']
        )->update();


        /* =========================
           INDEX LOGROS_USUARIO
           ========================= */

        $table = $this->table('Logros_Usuario');

        $table->addIndex(
            ['id_usuario','id_logro'],
            ['name' => 'idx_logros_usuario']
        )->update();


        /* =========================
           INDEX BIBLIOTECA
           ========================= */

        $table = $this->table('Biblioteca');

        $table->addIndex(
            ['id_usuario','id_videojuego'],
            ['name' => 'idx_biblioteca']
        )->update();
    }
}
