<?php

namespace AlphaSoft\Sql\Migration\Command;

use AlphaSoft\Sql\Migration\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SqlMigrationMigrateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sql:migration:migrate';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Start migration',
            '============',
            '',
        ]);

        $migration = Migration::create();
        $migration->migrate();
        foreach ($migration->getSuccessList() as $value) {
            $output->writeln($value . ' OK');
        }
        if ($migration->getSuccessList() == []) {
            $output->writeln('Nothing to migrate');
        }
        return Command::SUCCESS;
    }
}