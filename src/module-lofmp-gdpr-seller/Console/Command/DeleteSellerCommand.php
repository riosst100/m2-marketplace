<?php
namespace Lofmp\GdprSeller\Console\Command;

use Magento\Framework\App\State;
use Lofmp\GdprSeller\Model\SellerDeletionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteSellerCommand extends Command
{
    protected $service;
    protected $appState;

    public function __construct(
        SellerDeletionService $service,
        State $appState
    ) {
        $this->service = $service;
        $this->appState = $appState;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('tcg:delete:seller')
            ->setDescription('Delete sellers whose delete request date has expired');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Fix: Set area code
            try {
                $this->appState->setAreaCode('adminhtml');
            } catch (\Exception $e) {
                // Ignore "Area code is already set"
            }

            $count = $this->service->run();

            $output->writeln("<info>Deleted {$count} seller(s).</info>");

        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
        }

        return 0;
    }
}
