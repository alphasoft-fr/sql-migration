<?php

namespace AlphaSoft\Sql\Migration\Helper;

use Closure;
use InvalidArgumentException;
use PDO;

final class MigrationParams
{
    public static function validate(array $params): array
    {
        if (!array_key_exists('connection', $params)) {
            throw new InvalidArgumentException('The "connection" parameter is missing.');
        }

        if (!$params['connection'] instanceof PDO) {
            throw new InvalidArgumentException('The "connection" value must be an instance of PDO.');
        }

        if (!array_key_exists('migrations_directory', $params)) {
            throw new InvalidArgumentException('"migrations_directory" is missing');
        }

        if (array_key_exists('create_version_sql', $params) && !$params['create_version_sql'] instanceof Closure) {
            throw new InvalidArgumentException('"create_version_sql" value must be an instance of Closure');
        }

        $defaultParams = [
            'table_name' => 'mig_versions',
            'create_version_sql' => static function (string $table) {
                return 'CREATE TABLE IF NOT EXISTS ' . $table . ' (version VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL)';
            },
        ];
        return $params + $defaultParams;
    }
}
