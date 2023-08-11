<?php

namespace AlphaSoft\Sql\Migration\Command;

use AlphaSoft\Sql\Migration\Migration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SqlMigrationMigrateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'sql:migration:migrate';

    protected function configure(): void
    {
        $this->setDescription('Execute SQL migrations')
            ->setHelp('This command allows you to execute pending SQL migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Start migration');

        $migration = Migration::create();
        $failedMessage = null;

        try {
            $migration->migrate();
        } catch (\RuntimeException $e) {
            $failedMessage = $e->getMessage();
        }

        foreach ($migration->getSuccessList() as $value) {
            $io->success(sprintf('Migration version %s successfully applied', $value));
        }

        if ($failedMessage !== null) {
            $io->error($failedMessage);
            return Command::FAILURE;
        }

        if ($migration->getSuccessList() == []) {
            $io->info('No pending migrations');
        }

        return Command::SUCCESS;
    }
}
