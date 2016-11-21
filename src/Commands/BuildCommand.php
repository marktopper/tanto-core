<?php

namespace Tanto\Tanto\Commands;

use Tanto\Tanto\Contracts\SiteBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    /**
     * The SiteBuilder instance.
     *
     * @var SiteBuilder
     */
    protected $siteBuilder;

    /**
     * BuildCommand constructor.
     *
     * @param SiteBuilder $siteBuilder
     */
    public function __construct(SiteBuilder $siteBuilder)
    {
        $this->siteBuilder = $siteBuilder;

        parent::__construct();
    }

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Generate the site static files.')
            //->addOption('env', null, InputOption::VALUE_REQUIRED, 'Application Environment.', 'default')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Clear the cache before building.');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->siteBuilder->build(
            $input->getOption('force')
        );

        $output->writeln("<info>Site was generated successfully.</info>");
    }
}
