<?php
namespace OCA\DAV\Migrations;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use OCP\IDBConnection;
use OCP\Migration\ISchemaMigration;

/**
 * Updates column type from integer to bigint
 */
class Version20170711193427 implements ISchemaMigration {

	/** @var IDBConnection */
	private $connection;
	
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	public function changeSchema(Schema $schema, array $options) {
		$prefix = $options['tablePrefix'];

		if ($schema->hasTable("${prefix}properties")) {
			$table = $schema->getTable("{$prefix}properties");

			$idColumn = $table->getColumn('id');
			if ($idColumn){
				$idColumn->setType(Type::getType(Type::BIGINT));
				
				// Fixup postgres autoincrement
				if ($this->connection->getDatabasePlatform() instanceof PostgreSqlPlatform){
					$default = sprintf("nextval('%s_%s_seq'::regclass)", $table->getName(), $idColumn->getName());
					$idColumn->setDefault($default);
				}
				
				$idColumn->setOptions(['length' => 20]);
			}

			$fileidColumn = $table->getColumn('fileid');
			if ($fileidColumn){
				$fileidColumn->setType(Type::getType(Type::BIGINT));
				$fileidColumn->setOptions(['length' => 20]);
			}
		}
	}
}
