<?php

namespace AlphaSoft\Sql\Migration;

use AlphaSoft\Sql\Migration\Helper\MigrationParams;
use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use function date;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function is_array;

final class Migration
{
    private const CONFIG_FILENAME = 'migration-config.php';

    /** @var PDO The PDO connection instance. */
    private PDO $pdo;

    /** @var array Configuration parameters. */
    private array $params;

    /** @var array<string> List of successfully migrated versions. */
    private array $successList = [];
    /**
     * @var MigrationDirectory
     */
    private MigrationDirectory $directory;

    public static function create(): self
    {
        $filename = getcwd() . DIRECTORY_SEPARATOR . self::CONFIG_FILENAME;

        if (!file_exists($filename)) {
            throw new InvalidArgumentException("Configuration file '$filename' is missing");
        }

        $config = require $filename;

        if (!is_array($config)) {
            throw new InvalidArgumentException("Invalid configuration in '$filename'");
        }

        return new self($config);
    }

    /**
     * MigrateService constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $params = MigrationParams::validate($params);

        $this->pdo = $params['connection'];
        $this->directory = new MigrationDirectory($params['migrations_directory']);
        $this->params = $params;
    }

    public function generateMigration(): string
    {
        $file = date('YmdHis') . '.sql';
        $filename = $this->directory->getDir() . DIRECTORY_SEPARATOR . $file;

        $migrationContent = <<<'SQL'
-- UP MIGRATION --
-- Write the SQL code corresponding to the up migration here
-- You can add the necessary SQL statements for updating the database

-- DOWN MIGRATION --
-- Write the SQL code corresponding to the down migration here
-- You can add the necessary SQL statements for reverting the up migration
SQL;

        file_put_contents($filename, $migrationContent);
        return $filename;
    }

    public function migrate(): void
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createVersion();

        $stmt = $this->pdo->prepare('SELECT version FROM ' . $this->params['table_name']);
        $stmt->execute();
        $versions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($this->directory->getMigrations() as $version => $migration) {

            if (in_array($version, $versions)) {
                continue;
            }

            $this->up($version);
            $this->successList[] = $version;
        }
    }

    public function up(string $version): void
    {
        $migration = $this->directory->getMigration($version);
        try {

            $this->pdo->beginTransaction();
            foreach (explode(';'.PHP_EOL, self::contentUp($migration)) as $query) {
                $this->pdo->exec($query . ';');
            }

            $stmt = $this->pdo->prepare('INSERT INTO ' . $this->params['table_name'] . ' (`version`) VALUES (:version)');
            $stmt->execute(['version' => $version]);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to migrate version $version : " . $e->getMessage());
        }
    }

    public function down(string $version): void
    {
        $migration = $this->directory->getMigration($version);
        try {

            $this->pdo->beginTransaction();
            foreach (explode(';'.PHP_EOL, self::contentDown($migration)) as $query) {
                $this->pdo->exec($query . ';');
            }

            $stmt = $this->pdo->prepare('DELETE FROM ' . $this->params['table_name'] . ' WHERE version = :version');
            $stmt->execute(['version' => $version]);

            $this->pdo->commit();

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Failed to execute DOWN migration: " . $e->getMessage());
        }
    }

    private function createVersion(): void
    {
        $this->pdo->exec($this->params['create_version_sql']($this->params['table_name']));
    }

    private static function contentUp(string $migration): string
    {
        return trim(str_replace('-- UP MIGRATION --', '', self::content($migration)[0]));
    }

    private static function contentDown(string $migration): string
    {
        $downContent = self::content($migration)[1];
        return trim($downContent);
    }

    private static function content(string $migration): array
    {
        $migrationContent = file_get_contents($migration);
        $parts = explode('-- DOWN MIGRATION --', $migrationContent, 2);
        return [trim($parts[0]), (isset($parts[1]) ? trim($parts[1]) : '')];
    }

    public function getSuccessList(): array
    {
        return $this->successList;
    }
}
