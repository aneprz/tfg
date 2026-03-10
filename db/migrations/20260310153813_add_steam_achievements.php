<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSteamAchievements extends AbstractMigration
{
    public function change(): void
    {

        /* =========================
           VIDEOJUEGO → steam_appid
           ========================= */

        $this->table('Videojuego')
            ->addColumn('steam_appid', 'integer', [
                'null' => true,
                'signed' => false,
                'after' => 'id_videojuego'
            ])
            ->addIndex(['steam_appid'])
            ->update();


        /* =========================
           LOGROS → añadir info
           ========================= */

        $this->table('Logros')
            ->addColumn('id_videojuego', 'integer', [
                'null' => false,
                'signed' => false
            ])
            ->addColumn('icono', 'string', [
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('icono_gris', 'string', [
                'limit' => 255,
                'null' => true
            ])
            ->addColumn('porcentaje_global', 'decimal', [
                'precision' => 5,
                'scale' => 2,
                'null' => true
            ])
            ->addForeignKey('id_videojuego', 'Videojuego', 'id_videojuego', [
                'delete'=> 'CASCADE'
            ])
            ->addIndex(['id_videojuego'])
            ->update();

    }
}