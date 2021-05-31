<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Model;

use DenysUA\ReleaseDate\Api\ProductReleaseServiceInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class ProductReleaseService implements ProductReleaseServiceInterface
{
    const RELEASE_DAY_ATTRIBUTE_CODE = 'release_date_time';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ProductReleaseService constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function isAvalable(ProductInterface $product): bool
    {
        try {
            $releaseDateTime = $this->getProductReleaseDateTime($product);
            if (!$releaseDateTime) {
                return true;
            }

            return new \DateTime('now') >= $releaseDateTime;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Error duringcalculating release date %s', $e->getMessage()),
                ['product_id' => $product->getId()]
            );

            return true;
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return string|null
     */
    private function getProductReleaseDateAttributeValue(ProductInterface $product): ?string
    {
        $releaseAttrubute = $product->getCustomAttribute(self::RELEASE_DAY_ATTRIBUTE_CODE);
        if (!$releaseAttrubute || !$releaseAttrubute->getValue()) {
            return null;
        }

        return (string)$releaseAttrubute->getValue();
    }

    /**
     * @param ProductInterface $product
     *
     * @return \DateTime|null
     * @throws \Exception
     */
    public function getProductReleaseDateTime(ProductInterface $product): ?\DateTime
    {
        $releaseDate = $this->getProductReleaseDateAttributeValue($product);
        if (!$releaseDate) {
            return null;
        }

        return new \DateTime($releaseDate);
    }
}
