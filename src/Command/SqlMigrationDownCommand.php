<?php

namespace AlphaSoft\Sql\Migration\Command;

use AlphaSoft\Sql\Migration\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SqlMigrationDownCommand  extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sql:migration:down';

    protected function configure(): void
    {
        $this->setDescription('Execute the DOWN migration for a specific version.')
            ->addArgument('version', InputArgument::REQUIRED, 'The version of the migration to execute (DOWN).');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Executing Migration (DOWN)');

        $version = $input->getArgument('version');

        try {
            $migration = Migration::create();
            $migration->down($version);
            $io->success("Migration version $version executed successfully (DOWN).");
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
