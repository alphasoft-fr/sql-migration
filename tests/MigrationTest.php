<?php

namespace Test\AlphaSoft\Sql\Migration;

use AlphaSoft\Sql\Migration\Migration;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MigrationTest extends TestCase
{
    private static PDO $pdo;
    private static string $tempMigrationsDir;

    public static function setUpBeforeClass(): void
    {

        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        self::$tempMigrationsDir = __DIR__ . '/migrations';
    }

    public function tearDown(): void
    {
        // Clean up temporary directory after each test
        $folder = self::$tempMigrationsDir;
        array_map('unlink', glob("$folder/*.*"));
        self::$pdo->exec('DROP TABLE IF EXISTS mig_versions');
        self::$pdo->exec('DROP TABLE IF EXISTS test_table');
    }

    public function testMigration(): void
    {
        $migration = new Migration([
            'connection' => self::$pdo,
            'migrations_directory' => self::$tempMigrationsDir,
        ]);

        // Generate a sample migration file
        $migrationFile = $migration->generateMigration();
        $this->assertFileExists($migrationFile);

        file_put_contents($migrationFile, 'CREATE TABLE test_table (id INT)');

        // Run the migration
        $migration->migrate();

        // Check if the migration succeeded
        $successList = $migration->getSuccessList();
        $this->assertCount(1, $successList);
        $this->assertEquals(pathinfo($migrationFile, PATHINFO_FILENAME), $successList[0]); // Assuming the generated migration has this version
    }

    public function testInvalidConnectionParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "connection" value must be an instance of PDO.');

        new Migration([
            'connection' => 'invalid_connection', // Invalid PDO connection
            'migrations_directory' => self::$tempMigrationsDir,
        ]);
    }

    public function testRepeatedMigration(): void
    {
        $migration = new Migration([
            'connection' => self::$pdo,
            'migrations_directory' => self::$tempMigrationsDir,
        ]);

        // Generate a sample migration file
        $migrationFile = self::$tempMigrationsDir . '/20230811000002.sql';
        file_put_contents($migrationFile, 'CREATE TABLE test_table (id INT)');

        // Run the migration once
        $migration->migrate();

        // Run the migration again
        $migration->migrate();

        // Check if the migration succeeded
        $successList = $migration->getSuccessList();
        $this->assertCount(1, $successList);
    }

    public function testSuccessListAndException(): void
    {
        $migration = new Migration([
            'connection' => self::$pdo,
            'migrations_directory' => self::$tempMigrationsDir,
        ]);

        // Generate a sample migration file
        $migrationFile = self::$tempMigrationsDir . '/20230811000003.sql';
        file_put_contents($migrationFile, 'CREATE TABLE test_table (id INT)');

        // Run the migration
        $migration->migrate();

        // Check if the migration succeeded
        $successList = $migration->getSuccessList();
        $this->assertCount(1, $successList);
        $this->assertEquals('20230811000003', $successList[0]);

        // Generate another migration file with an invalid SQL query
        $invalidMigrationFile = self::$tempMigrationsDir . '/20230811000004.sql';
        file_put_contents($invalidMigrationFile, 'INVALID SQL QUERY');

        // Test if an exception is thrown for the invalid migration
        $this->expectException(RuntimeException::class);

        $migration->migrate();
    }

    public function testDownMigration(): void
    {
        // Setup
        $migration = new Migration([
            'connection' => self::$pdo,
            'migrations_directory' => self::$tempMigrationsDir,
        ]);

        // Generate a sample migration file with up and down migrations
        $migrationFile = self::$tempMigrationsDir . '/20230811000005.sql';
        file_put_contents($migrationFile, "-- UP MIGRATION --\nCREATE TABLE test_table (id INT)\n\n-- DOWN MIGRATION --\nDROP TABLE test_table");

        // Run the migration
        $migration->migrate();

        // Check if the migration succeeded
        $successList = $migration->getSuccessList();
        $this->assertCount(1, $successList);
        $this->assertEquals('20230811000005', $successList[0]); // Assuming the generated migration has this version

        // Run the down migration
        $migration->down('20230811000005');

        // Check if the table was dropped
        $stmt = self::$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='test_table'");
        $this->assertFalse($stmt->fetchColumn());

    }

    public function testDownEmptyMigration(): void
    {
        // Setup
        $migration = new Migration([
            'connection' => self::$pdo,
            'migrations_directory' => self::$tempMigrationsDir,
        ]);

        // Generate a sample migration file with up and down migrations
        $migrationFile = self::$tempMigrationsDir . '/20230811000006.sql';
        file_put_contents($migrationFile, "-- UP MIGRATION --\nCREATE TABLE test_table (id INT)\n\n");

        // Run the migration
        $migration->migrate();

        // Check if the migration succeeded
        $successList = $migration->getSuccessList();
        $this->assertCount(1, $successList);
        $this->assertEquals('20230811000006', $successList[0]); // Assuming the generated migration has this version


        $this->expectException(RuntimeException::class);
        // Run the down migration
        $migration->down('20230811000006');

    }
}
