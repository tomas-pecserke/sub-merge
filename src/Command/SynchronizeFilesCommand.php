<?php

namespace App\Command;

use App\Configuration\Configuration;
use App\Configuration\Property;
use App\VideoFiles\FileSynchronizationResult;
use App\VideoFiles\Synchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'files:sync', description: 'Synchronize monitored video files')]
class SynchronizeFilesCommand extends Command {
    private readonly Configuration $config;
    private readonly Synchronizer $synchronizer;

    public function __construct(Configuration $config, Synchronizer $synchronizer) {
        $this->config = $config;
        $this->synchronizer = $synchronizer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        set_time_limit(0);

        $dirs = $this->config->getArray(Property::WatchedDirs, []);
        $dirCount = count($dirs);
        $output->writeln("Synchronizing monitored files in $dirCount directories:");
        foreach ($dirs as $dir) {
            $output->write(' - ');
            $output->writeln($dir);
        }
        $output->writeln('');

        $result = $this->synchronizer->sync();

        $output->writeln('Results:');
        $output->writeln($result[FileSynchronizationResult::Discovered->name] . ' new files');
        $output->writeln($result[FileSynchronizationResult::Present->name] . ' existing files');
        $output->writeln($result[FileSynchronizationResult::Changed->name] . ' changed files');
        $output->writeln($result[FileSynchronizationResult::Removed->name] . ' removed files');

        return Command::SUCCESS;
    }
}
