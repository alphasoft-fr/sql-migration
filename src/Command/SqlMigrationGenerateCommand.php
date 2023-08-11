<?php

namespace AlphaSoft\Sql\Migration\Command;

use AlphaSoft\Sql\Migration\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SqlMigrationGenerateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sql:migration:generate';

    protected function configure(): void
    {
        $this->setDescription('Generate a new migration file')
            ->setHelp('This command allows you to generate a new SQL migration file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate Migration File');

        $migration = Migration::create();
        $filename = $migration->generateMigration();

        $io->success(sprintf('Migration file %s generated', $filename));

        return Command::SUCCESS;
    }
}