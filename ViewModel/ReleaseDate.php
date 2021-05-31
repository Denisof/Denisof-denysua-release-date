<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\ViewModel;

use DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class ReleaseDate implements \Magento\Framework\View\Element\Block\ArgumentInterface
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
     * ReleaseDate constructor.
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
     * @param ProductInterface $product
     *
     * @return string|null
     */
    public function getReleaseDate(ProductInterface $product): ?string
    {
        if ($this->productReleaseService->isAvalable($product)) {
            return null;
        }
        $releaseDate = $this->productReleaseService->getProductReleaseDateTime($product);
        $releaseDate->setTimezone($this->timeZone->getCurrentTimeZone());

        return $releaseDate->format('Y-m-d H:i | e');
    }
}
