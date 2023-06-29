<?php

namespace AlphaSoft\Sql\Migration;

final class Migration
{
    private const  CONFIG_FILENAME = 'sql-migration.php';
    private ?\PDO $pdo;
    private array $params;

    /***
     * @var array<string>
     */
    private array $successList = [];

    public static function create(): self
    {
        $filename = getcwd() . DIRECTORY_SEPARATOR . self::CONFIG_FILENAME;
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException($filename . ' is missing');
        }
        return new self(require $filename);
    }

    /**
     * MigrateService constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (!array_key_exists('connection', $params)) {
            throw new \InvalidArgumentException('"connection" is missing');
        }

        if (!$params['connection'] instanceof \PDO) {
            throw new \InvalidArgumentException('"connection" value must be an instance of PDO');
        }

        if (!array_key_exists('migrations_directory', $params)) {
            throw new \InvalidArgumentException('"migrations_directory" is missing');
        }

        if (array_key_exists('create_version_sql', $params)  && !$params['create_version_sql'] instanceof \Closure) {
            throw new \InvalidArgumentException('"create_version_sql" value must be an instance of Closure');
        }

        $this->pdo = $params['connection'];
        $defaultParams = [
            'table_name' => 'migration_versions',
            'create_version_sql' => static function (string $tableName) {
                return 'CREATE TABLE IF NOT EXISTS ' . $tableName. ' (version varchar(255) NOT NULL)';
            },
        ];
        $this->params = $defaultParams + $params;
    }

    public function generateMigration(): string
    {
        $file = date('YmdHis') . '.sql';
        $filename = $this->params['migrations_directory'] . DIRECTORY_SEPARATOR . $file;
        file_put_contents($filename, '');
        return $filename;
    }

    public function migrate(): void
    {
        $this->createVersion();
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $stmt = $this->pdo->prepare('SELECT version FROM ' . $this->params['table_name']);
        $stmt->execute();
        $versions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($this->getMigrations() as $version => $migration) {

            if (in_array($version, $versions)) {
                continue;
            }

            $this->pdo->query(file_get_contents($migration));
            $this->pdo->prepare('INSERT INTO ' . $this->params['table_name'] . ' (`version`) VALUES (:version)')
                ->execute(['version' => $version]);

            $this->successList[] = $version;
        }

    }

    public function createVersion(): void
    {
        $this->pdo->query($this->params['create_version_sql']($this->params['table_name']));
    }

    private function getMigrations(): array
    {
        $migrations = [];
        foreach (new \DirectoryIterator($this->params['migrations_directory']) as $file) {
            if ($file->getExtension() !== 'sql') {
                continue;
            }
            $version = pathinfo($file->getBasename(), PATHINFO_FILENAME);
            $migrations[$version] = $file->getPathname();
        }
        ksort($migrations);
        return $migrations;
    }

    public function getSuccessList(): array
    {
        return $this->successList;
    }
}