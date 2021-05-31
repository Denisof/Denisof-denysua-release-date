<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Observer;

use Magento\Framework\Event\Observer;

class ReleaseDate implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface
     */
    private $productReleaseService;

    /**
     * ReleaseDate constructor.
     *
     * @param \DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface $productReleaseService
     */
    public function __construct(\DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface $productReleaseService)
    {
        $this->productReleaseService = $productReleaseService;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $salable = $observer->getEvent()->getSalable();
        if(!$salable || !$salable->getIsSalable()){
            return;
        }
        $product = $salable->getProduct();
        if(!$this->productReleaseService->isAvalable($product)) {
            $salable->setIsSalable(false);
        }
    }
}
