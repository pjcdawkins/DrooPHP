<?php
/**
 * @file
 * Command-line application for counting an election.
 *
 * See http://symfony.com/doc/current/components/console/single_command_tool.html
 */

namespace DrooPHP\Cli;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class CountApplication extends Application {

  /**
   * @{inheritdoc}
   */
  protected function getCommandName(InputInterface $input) {
    return 'count';
  }

  /**
   * @{inheritdoc}
   */
  protected function getDefaultCommands() {
    $defaultCommands = parent::getDefaultCommands();
    $defaultCommands[] = new CountCommand();
    return $defaultCommands;
  }

  /**
   * @{inheritdoc}
   */
  public function getDefinition() {
    $inputDefinition = parent::getDefinition();
    $inputDefinition->setArguments();
    return $inputDefinition;
  }

}
