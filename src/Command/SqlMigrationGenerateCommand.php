<?php

namespace AlphaSoft\Sql\Migration\Command;

use AlphaSoft\Sql\Migration\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SqlMigrationGenerateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sql:migration:generate';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Generate file migration',
            '============',
            '',
        ]);

        $migration = Migration::create();
        $filename = $migration->generateMigration();
        $output->writeln($filename.' generated');
        return Command::SUCCESS;
    }
}