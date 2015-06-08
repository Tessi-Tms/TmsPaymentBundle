<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPayboxBinKeyCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tms-payment:paybox:build-binary-key')
            ->setDescription('Build paybox binary key')
            ->addArgument('site', InputArgument::REQUIRED, 'The site id that will identify the built key')
            ->addArgument('key', InputArgument::REQUIRED, 'The key to transform in binary and store in keys path')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command.

To build a binary key:

<info>php app/console %command.name% "MySiteId" "MyPayboxKey"</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $site = $input->getArgument('site');
        $key  = $input->getArgument('key');
        $output->writeln('<comment>Build binary key</comment>');
        $binKey = pack("H*", $key);

        $keyPath = $this
            ->getContainer()
            ->get('tms_payment.backend_registry')
            ->getBackend('paybox')
            ->getKeyPath($site)
        ;

        if (file_put_contents($keyPath, $binKey)) {
            $output->writeln(sprintf(
                '<info>Binary built: %s</info>',
                $keyPath
            ));
        } else {
            $output->writeln('<error>Binary not built</error>');
        }
    }
}