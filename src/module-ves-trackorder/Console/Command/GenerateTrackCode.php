<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * https://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Trackorder
 * @copyright  Copyright (c) 2021 Venustheme (https://www.venustheme.com/)
 * @license    https://www.venustheme.com/LICENSE-1.0.html
 */


namespace Ves\Trackorder\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class GenerateTrackCode extends Command
{

    const NAME_ARGUMENT = "status";
    const NAME_OPTION = "option";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    protected $_generateHelper;
    protected $appState;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource 
     * @param \Ves\Trackorder\Helper\Generate $generateHelper
     * @param \Magento\Framework\App\State $appState
     * 
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Ves\Trackorder\Helper\Generate $generateHelper,
        \Magento\Framework\App\State $appState
        ) {
        $this->_resource = $resource;
        $this->_generateHelper = $generateHelper;
        $this->appState = $appState;
        parent::__construct();
        
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        $this->appState->emulateAreaCode(
            "frontend",
            [$this, "executeCallBack"],
            [$input, $output]
        );
    }
    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    public function executeCallBack(InputInterface $input, OutputInterface $output)
    {
        try {
            $statusArg = mb_strtolower((string)$input->getArgument('status'));
            $orderStatus = ($statusArg && $statusArg !== 'all') ? $statusArg : '';
            $results = $this->_generateHelper->runGenerate($orderStatus);
            //$output->writeln($results);
            $output->writeln("Generated Missing Order Tracking Code sucessfully.");
        } catch (\Exception $e) {
            $output->writeln("Generated Missing Order Tracking Code sucessfully.");
            $output->writeln("Trace:");
            $output->writeln($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("vestrackorder:generate");
        $this->setDescription("Generate Missing Order Tracking Code.");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Order Status"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}
