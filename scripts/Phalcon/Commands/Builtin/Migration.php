<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Developer Tools                                                |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Commands\Builtin;

use Phalcon\Builder;
use Phalcon\Builder\BuilderException;
use Phalcon\Script\Color;
use Phalcon\Commands\Command;
use Phalcon\Migrations;
use Phalcon\Config;
use Phalcon\Config\Adapter\Ini as IniConfig;
use Phalcon\Config\Adapter\Json as JsonConfig;
use Phalcon\Config\Adapter\Yaml as YamlConfig;

/**
 * Migration Command
 *
 * Generates/Run a migration
 *
 * @package     Phalcon\Commands\Builtin
 * @copyright   Copyright (c) 2011-2015 Phalcon Team (team@phalconphp.com)
 * @license     New BSD License
 */
class Migration extends Command
{

    protected $_possibleParameters = array(
        'action=s'          => "Generates a Migration [generate|run].",
        'config=s'          => "Configuration file.",
        'migrations=s'      => "Migrations directory.",
        'directory=s'       => "Directory where the project was created.",
        'table=s'           => "Table to migrate. Default: all.",
        'version=s'         => "Version to migrate.",
        'force'             => "Forces to overwrite existing migrations.",
        'no-auto-increment' => "Disable auto increment (Generating only).",
        'data=s'            => "Export data [always|oncreate] (Import data when run migration).",
    );

    /**
     * Executes the command
     *
     * @param $parameters
     * @return void
     */
    public function run($parameters)
    {
        if ($this->isReceivedOption('table')) {
            $tableName = $this->getOption('table');
        } else {
            $tableName = 'all';
        }

        $path = '';
        if ($this->isReceivedOption('directory')) {
            $path = $this->getOption('directory');
        }

        $path = realpath($path) . DIRECTORY_SEPARATOR;

        if ($this->isReceivedOption('config')) {
            $config = $this->loadConfig($path . $this->getOption('config'));
        } else {
            $config = $this->getConfig($path);
        }

        if ($this->isReceivedOption('migrations')) {
            $migrationsDir = $path.$this->getOption('migrations');
        } elseif (isset($config['application']['migrationsDir'])) {
            $migrationsDir = $config['application']['migrationsDir'];
            if (!$this->path->isAbsolutePath($migrationsDir)) {
                $migrationsDir = $path . $migrationsDir;
            }
        } else {
            if (file_exists($path.'app')) {
                $migrationsDir = $path.'app/migrations';
            } elseif (file_exists($path.'apps')) {
                $migrationsDir = $path.'apps/migrations';
            } else {
                $migrationsDir = $path.'migrations';
            }
        }

        $exportData = $this->getOption('data');
        $originalVersion = $this->getOption('version');

        $action = $this->getOption(array('action', 1));

        $version = $this->getOption('version');

        if ($action == 'generate') {
            Migrations::generate(array(
                'directory'       => $path,
                'tableName'       => $tableName,
                'exportData'      => $exportData,
                'migrationsDir'   => $migrationsDir,
                'originalVersion' => $originalVersion,
                'force'           => $this->isReceivedOption('force'),
                'no-ai'           => $this->isReceivedOption('no-auto-increment'),
                'config'          => $config
            ));
        } else {
            if ($action == 'run') {
                Migrations::run(array(
                    'directory'     => $path,
                    'tableName'     => $tableName,
                    'migrationsDir' => $migrationsDir,
                    'force'         => $this->isReceivedOption('force'),
                    'config'        => $config,
                    'version'       => $version,
                ));
            }
        }
    }

    /**
     * Returns the command identifier
     *
     * @return array
     */
    public function getCommands()
    {
        return array('migration', 'create-migration');
    }

    /**
     * Checks whether the command can be executed outside a Phalcon project
     */
    public function canBeExternal()
    {
        return false;
    }

    /**
     * Prints the help for current command.
     *
     * @return void
     */
    public function getHelp()
    {
        print Color::head('Help:') . PHP_EOL;
        print Color::colorize('  Generates/Run a Migration') . PHP_EOL . PHP_EOL;

        print Color::head('Usage: Generate a Migration') . PHP_EOL;
        print Color::colorize('  migration generate', Color::FG_GREEN) . PHP_EOL . PHP_EOL;

        print Color::head('Usage: Run a Migration') . PHP_EOL;
        print Color::colorize('  migration run', Color::FG_GREEN) . PHP_EOL . PHP_EOL;

        print Color::head('Arguments:') . PHP_EOL;
        print Color::colorize('  ?', Color::FG_GREEN);
        print Color::colorize("\tShows this help text") . PHP_EOL . PHP_EOL;

        $this->printParameters($this->_possibleParameters);
    }

    /**
     * Returns number of required parameters for this command
     *
     * @return integer
     */
    public function getRequiredParams()
    {
        return 1;
    }
}
