<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueAchievementDescriptionPerGame extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            CREATE UNIQUE INDEX unique_game_achievement_description
            ON Logros (id_videojuego, descripcion(255))
        ");
    }
}