<?php

namespace Test\AlphaSoft\Sql\Migration;

use AlphaSoft\Sql\Migration\Migration;
use PHPUnit\Framework\TestCase;

class MigrationContentTest extends TestCase
{
    private static string $tempMigrationsDir;

    public static function setUpBeforeClass(): void
    {
        self::$tempMigrationsDir = __DIR__ . '/migrations';

    }

    public function tearDown(): void
    {
        // Clean up temporary directory after each test
        $folder = self::$tempMigrationsDir;
        array_map('unlink', glob("$folder/*.*"));
    }

    public function testContentUpAndDown()
    {
        $migrationFile = self::$tempMigrationsDir . '/20230811000005.sql';
        file_put_contents($migrationFile, "-- UP MIGRATION --\nCREATE TABLE test_table (id INT)\n\n-- DOWN MIGRATION --\nDROP TABLE test_table");

        $reflection = new \ReflectionClass(Migration::class);
        $contentUpMethod = $reflection->getMethod('contentUp');
        $contentUpMethod->setAccessible(true);

        $contentDownMethod = $reflection->getMethod('contentDown');
        $contentDownMethod->setAccessible(true);

        $upContent = $contentUpMethod->invokeArgs(null, [$migrationFile]);
        $downContent = $contentDownMethod->invokeArgs(null, [$migrationFile]);

        $this->assertEquals("CREATE TABLE test_table (id INT)", $upContent);
        $this->assertEquals("DROP TABLE test_table", $downContent);
    }

    public function testContentUpOnly()
    {
        $migrationFile = self::$tempMigrationsDir . '/20230811000006.sql';
        file_put_contents($migrationFile, "-- UP MIGRATION --\nCREATE TABLE test_table (id INT)");

        $reflection = new \ReflectionClass(Migration::class);
        $contentUpMethod = $reflection->getMethod('contentUp');
        $contentUpMethod->setAccessible(true);

        $contentDownMethod = $reflection->getMethod('contentDown');
        $contentDownMethod->setAccessible(true);

        $upContent = $contentUpMethod->invokeArgs(null, [$migrationFile]);
        $downContent = $contentDownMethod->invokeArgs(null, [$migrationFile]);

        $this->assertEquals("CREATE TABLE test_table (id INT)", $upContent);
        $this->assertEquals("", $downContent);
    }


    public function testContentUp()
    {
        $migrationFile = self::$tempMigrationsDir . '/20230811000007.sql';
        file_put_contents($migrationFile, "CREATE TABLE test_table (id INT)");

        $reflection = new \ReflectionClass(Migration::class);
        $contentUpMethod = $reflection->getMethod('contentUp');
        $contentUpMethod->setAccessible(true);

        $contentDownMethod = $reflection->getMethod('contentDown');
        $contentDownMethod->setAccessible(true);

        $upContent = $contentUpMethod->invokeArgs(null, [$migrationFile]);
        $downContent = $contentDownMethod->invokeArgs(null, [$migrationFile]);

        $this->assertEquals("CREATE TABLE test_table (id INT)", $upContent);
        $this->assertEquals("", $downContent);
    }

    public function testContentUpAndDownMultipleLine()
    {
        $migrationFile = self::$tempMigrationsDir . '/20230811000008.sql';
        file_put_contents($migrationFile, "-- UP MIGRATION --\nCREATE TABLE test_table1 (id INT)\nCREATE TABLE test_table2 (id INT)\n\n-- DOWN MIGRATION --\nDROP TABLE test_table1\nDROP TABLE test_table2");

        $reflection = new \ReflectionClass(Migration::class);
        $contentUpMethod = $reflection->getMethod('contentUp');
        $contentUpMethod->setAccessible(true);

        $contentDownMethod = $reflection->getMethod('contentDown');
        $contentDownMethod->setAccessible(true);

        $upContent = $contentUpMethod->invokeArgs(null, [$migrationFile]);
        $downContent = $contentDownMethod->invokeArgs(null, [$migrationFile]);

        $expectedUpContent = "CREATE TABLE test_table1 (id INT)\nCREATE TABLE test_table2 (id INT)";
        $expectedDownContent = "DROP TABLE test_table1\nDROP TABLE test_table2";

        $this->assertEquals($expectedUpContent, $upContent);
        $this->assertEquals($expectedDownContent, $downContent);
    }
}
