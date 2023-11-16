# SQL Migration Utility
This is a utility library and commands for managing database migrations in SQL.

[![Latest Stable Version](http://poser.pugx.org/alphasoft-fr/sql-migration/v)](https://packagist.org/packages/alphasoft-fr/sql-migration) [![Total Downloads](http://poser.pugx.org/alphasoft-fr/sql-migration/downloads)](https://packagist.org/packages/alphasoft-fr/sql-migration) [![Latest Unstable Version](http://poser.pugx.org/alphasoft-fr/sql-migration/v/unstable)](https://packagist.org/packages/alphasoft-fr/sql-migration) [![License](http://poser.pugx.org/alphasoft-fr/sql-migration/license)](https://packagist.org/packages/alphasoft-fr/sql-migration) [![PHP Version Require](http://poser.pugx.org/alphasoft-fr/sql-migration/require/php)](https://packagist.org/packages/alphasoft-fr/sql-migration)
## Installation
Use [Composer](https://getcomposer.org/)

### Composer Require
```
composer require alphasoft-fr/sql-migration
```

## Requirements

* PHP version 8.1

## Usage

### Configuration

1. Create a configuration file named `migration-config.php` at the root of your project. You can use the following example as a starting point:

```php
<?php

return [
    'connection' => new PDO('mysql:host=localhost;dbname=mydb', 'username', 'password'),
    'migrations_directory' => __DIR__ . '/migrations',
    // Other configuration options...
];
```

2. Customize the configuration options according to your needs. You can provide the PDO connection instance and specify the directory where migration files will be stored.

### Generating a Migration File

You can generate a new migration file using the following command:

```shell
php vendor/bin/sqlmigration sql:migration:generate
```

This will create a new migration file in the specified migrations directory with placeholder content for both the up and down migrations.

Modify the generated migration file with SQL queries corresponding to the intended migration:

```sql
-- UP MIGRATION --
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- DOWN MIGRATION --
DROP TABLE users;
```

### Running Migrations

You can apply pending migrations using the following command:

```shell
php vendor/bin/sqlmigration sql:migration:migrate
```

This command will execute all pending migrations in ascending order of their version numbers. Successfully applied migrations will be displayed in the console output.

### Rolling Back Migrations

You can revert the last applied migration using the following command:

```shell
php vendor/bin/sqlmigration sql:migration:down <version>
```

Replace `<version>` with the version number of the migration you want to revert. This command will execute the down migration for the specified version, effectively rolling back the changes made by that migration.

## Contributing

If you encounter any issues or have suggestions for improvements, please feel free to open an issue on the GitHub repository.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---
