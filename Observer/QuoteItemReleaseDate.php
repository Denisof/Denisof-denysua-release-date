<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Observer;

use DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;

class QuoteItemReleaseDate implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ProductReleaseServiceInterface
     */
    private $productReleaseService;

    /**
     * @var \DenysUA\ReleaseDate\Model\TimeZone
     */
    private $timeZone;

    /**
     * QuoteItemReleaseDate constructor.
     *
     * @param ProductReleaseServiceInterface      $productReleaseService
     * @param \DenysUA\ReleaseDate\Model\TimeZone $timeZone
     */
    public function __construct(
        ProductReleaseServiceInterface $productReleaseService,
        \DenysUA\ReleaseDate\Model\TimeZone $timeZone
    ) {
        $this->productReleaseService = $productReleaseService;
        $this->timeZone = $timeZone;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$quoteItem) {
            return;
        }
        $product = $quoteItem->getProduct();
        if (!$product) {
            return;
        }
        if ($this->productReleaseService->isAvalable($product)) {
            return;
        }
        $quoteItem->setHasError(true);
        $releaseDate = $this->productReleaseService->getProductReleaseDateTime($product);
        $releaseDate->setTimezone($this->timeZone->getCurrentTimeZone());
        $quoteItem->setMessage(__("Product will be available on %1", $releaseDate->format('Y-m-d H:i | e')));
    }
}
