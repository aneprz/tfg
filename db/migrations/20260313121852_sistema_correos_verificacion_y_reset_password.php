<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SistemaCorreosVerificacionYResetPassword extends AbstractMigration
{
	public function change(): void
	{
		$table = $this->table('Usuario');

		$table->addColumn('email_verificado', 'boolean', [
			'default' => false,
			'after' => 'email'
		]);

		$table->addColumn('token_verificacion', 'string', [
			'limit' => 255,
			'null' => true,
			'after' => 'email_verificado'
		]);

		$table->addColumn('token_verificacion_expira', 'datetime', [
			'null' => true,
			'after' => 'token_verificacion'
		]);

		$table->addColumn('token_reset_password', 'string', [
			'limit' => 255,
			'null' => true
		]);

		$table->addColumn('token_reset_expira', 'datetime', [
			'null' => true
		]);
		$table->update();
	}
}