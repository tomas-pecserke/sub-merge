<?php

namespace App\Command;

use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[AsCommand(name: 'srt:merge', description: 'Merge subtitles into a video file')]
class SubtitlesMergeCommand extends Command {
  private const VIDEO_INPUT_PATH_OPTION = 'input';
  private const VIDEO_OUTPUT_PATH_OPTION = 'output';
  private const SUBTITLES_PATH_OPTION = 'subtitles';
  private const SUBTITLES_LANGUAGE_OPTION = 'language';
  private const SUBTITLES_DEFAULT_OPTION = 'default';
  private const SUBTITLES_CHARSET_OPTION = 'charset';
  private const FORCE_OVERWRITE_OPTION = 'force';

  private const REQUIRED_OPTIONS = [
    self::VIDEO_INPUT_PATH_OPTION,
    self::VIDEO_OUTPUT_PATH_OPTION
  ];

  protected function configure(): void {
    $this->setHelp('Merge subtitles into a video file')
      ->setDefinition([
        new InputOption(self::VIDEO_INPUT_PATH_OPTION, 'i', InputOption::VALUE_REQUIRED, 'Video input file path'),
        new InputOption(self::VIDEO_OUTPUT_PATH_OPTION, 'o', InputOption::VALUE_REQUIRED, 'Video output file path'),
        new InputOption(self::SUBTITLES_PATH_OPTION, 's', InputOption::VALUE_REQUIRED, 'Subtitles file path'),
        new InputOption(self::SUBTITLES_LANGUAGE_OPTION, 'l', InputOption::VALUE_REQUIRED, 'Subtitles locale'),
        new InputOption(self::SUBTITLES_DEFAULT_OPTION, 'd', InputOption::VALUE_NEGATABLE, 'Whether to add subtitles as default track'),
        new InputOption(self::SUBTITLES_CHARSET_OPTION, 'c', InputOption::VALUE_NEGATABLE, 'Subtitles file charset', 'utf-8'),
        new InputOption(self::FORCE_OVERWRITE_OPTION, 'f', InputOption::VALUE_NONE, 'Force overwrite if output path exists')
      ]);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    foreach (self::REQUIRED_OPTIONS as $option) {
      if (!$input->hasOption($option)) {
        $output->writeln("<error>Required option '$option' is missing.</error>");
        return Command::INVALID;
      }
    }

    $fs = new Filesystem();

    $inputPath = $input->getOption(self::VIDEO_INPUT_PATH_OPTION);
    if (!$fs->exists($inputPath)) {
      $output->writeln("<error>Input file path '$inputPath' does not exist.</error>");
      return Command::INVALID;
    }
    $inputPath = Path::makeAbsolute(Path::canonicalize($inputPath), getcwd());

    $outputPath = $input->getOption(self::VIDEO_OUTPUT_PATH_OPTION);
    $forceOverwrite = $input->hasOption(self::FORCE_OVERWRITE_OPTION);
    if ($fs->exists($outputPath) && !is_dir($outputPath) && !$forceOverwrite) {
      if (!$input->isInteractive()) {
        $output->writeln('<error>Output file already exists.</error>');
        return Command::FAILURE;
      }

      /* @var QuestionHelper $questionHelper */
      $questionHelper = $this->getHelper('question');
      $question = new ConfirmationQuestion('Overwrite output file?', false);
      if (!$questionHelper->ask($input, $output, $question)) {
        return Command::SUCCESS;
      }
    }
    $outputPath = Path::makeAbsolute(Path::canonicalize($outputPath), getcwd());

    $subtitlesPath = $input->hasOption(self::SUBTITLES_PATH_OPTION)
      ? $input->getOption(self::SUBTITLES_PATH_OPTION)
      : '';
    $subtitlesPath = $subtitlesPath !== '' && $fs->exists($subtitlesPath)
      ? Path::makeAbsolute(Path::canonicalize($subtitlesPath), getcwd())
      : $this->findSubtitlesForVideo($inputPath, getcwd());

    $subtitlesLanguage = $input->getOption(self::SUBTITLES_LANGUAGE_OPTION);

    return Command::SUCCESS;
  }

  private function findSubtitlesForVideo(string $videoPath, string $workDir): string|null {
    return null;
  }
}
