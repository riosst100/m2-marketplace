<?php
namespace Lofmp\GdprSeller\Cron;

use Lofmp\GdprSeller\Model\SellerDeletionService;

class DeleteSellers
{
    protected $service;

    public function __construct(SellerDeletionService $service)
    {
        $this->service = $service;
    }

    public function execute()
    {
        $this->service->run();
        return $this;
    }
}
