<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueSteamAchievements extends AbstractMigration
{
    public function change(): void
    {
        $this->table('Logros')
            ->addIndex(
                ['id_videojuego', 'steam_api_name'],
                ['unique' => true, 'name' => 'unique_game_achievement']
            )
            ->update();
    }
}