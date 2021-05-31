<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Api;

use Magento\Catalog\Api\Data\ProductInterface;

interface ProductReleaseServiceInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function isAvalable(ProductInterface $product): bool;

    /**
     * @param ProductInterface $product
     *
     * @return \DateTime|null
     */
    public function getProductReleaseDateTime(ProductInterface $product): ?\DateTime;
}
