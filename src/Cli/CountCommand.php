<?php
/**
 * @file
 * Command-line interface.
 */

namespace DrooPHP\Cli;

use DrooPHP\Count;
use DrooPHP\Formatter\Text;
use DrooPHP\Method\Stv;
use DrooPHP\Source\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CountCommand extends Command {

  /**
   * @{inheritdoc}
   */
  protected function configure() {
    $this->setName('count')
      ->setDescription('Count the votes in an election')
      ->addArgument('filename',
        InputArgument::REQUIRED,
        'The name of the ballot file'
      )
      ->addOption('method', 'm', InputOption::VALUE_REQUIRED,
        'The counting method (default: STV).'
      )
      ->addOption('format', 'f', InputOption::VALUE_REQUIRED,
        'The output format (default: text).'
      )
      ->addOption('allow-invalid', 'i', InputOption::VALUE_NONE,
        'Enable to allow invalid ballots.'
      )
      ->addOption('allow-equal', 'e', InputOption::VALUE_NONE,
        'Enable to allow equal rankings.'
      )
      ->addOption('allow-repeat', 'r', InputOption::VALUE_NONE,
        'Enable to allow repeat rankings.'
      )
      ->addOption('allow-skipped', 's', InputOption::VALUE_NONE,
        'Enable to allow skipped rankings.'
      );
  }

  /**
   * @{inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Set up options for processing the ballot file.
    $source = new File([
      'filename' => $input->getArgument('filename'),
      'allow_invalid' => $input->getOption('allow-invalid'),
    ]);
    if (!$input->getOption('allow-invalid')) {
      $source->setOption('allow_equal', $input->getOption('allow-equal'));
      $source->setOption('allow_repeat', $input->getOption('allow-repeat'));
      $source->setOption('allow_skipped', $input->getOption('allow-skipped'));
    }

    // Set up the count.
    $count = new Count([
      'formatter' => $input->getOption('format') ? : new Text(),
      'source' => $source,
      'method' => $input->getOption('method') ? : new Stv(),
    ]);

    // Run the count, and output the result.
    $output->writeln($count->getOutput());
  }

}
