<?php
namespace wapmorgan\ServerAvailabilityMonitor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MonitorCommand extends Command {

	protected function configure() {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('monitor')

        // the short description shown while running "php bin/console list"
        ->setDescription('Monitors all servers.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to monitor all configured servers.')

        ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The location of config-file', ServersList::getDefaultConfigLocation())
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $config_file = $input->getOption('config') ?: ServersList::getDefaultConfigLocation();
        $servers_list = new ServersList($config_file);

        $check_period = 10;
        $servers_list->initializeServers();

        while (true) {
            $errors = [];
            foreach ($servers_list->getServerNames() as $server_name) {
                if ($output->isDebug())
                    $output->writeln('Checking '.$server_name);
                $server = $servers_list->getServer($server_name);

                $result = $server->checkAvailability();
                if ($result !== true) {
                    $errors[$server_name] = $result;
                    if ($output->isVeryVerbose())
                        $output->writeln('<error>Server check failed</error>');
                }
            }
            if (empty($errors)) {
                if ($output->isVerbose())
                    $output->writeln('<info>Check at '.date('r').': all servers successfull</info>');
            }
            else {
                $output->writeln('<info>Check at '.date('r').': '.count($errors).' error'.(count($errors) > 1 ? 's' : null).'</info>');
                foreach ($errors as $server_name => $error) {
                    $output->writeln('<error>'.$server_name.' reported error: '.$error->getMessage().'</error>');
                }
            }
            sleep($check_period);
        }
    }
}
