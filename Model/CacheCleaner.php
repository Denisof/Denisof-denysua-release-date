<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Model;

use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Indexer\CacheContextFactory;
use Psr\Log\LoggerInterface;

class CacheCleaner
{
    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var FrontendPool
     */
    private $frontendPool;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $productCacheTag;

    /**
     * @var CacheContextFactory
     */
    private $cacheContextFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $cacheTypes;

    /**
     * CacheCleaner constructor.
     *
     * @param FrontendPool        $frontendPool
     * @param EventManager        $eventManager
     * @param CacheContextFactory $cacheContextFactory
     * @param LoggerInterface     $logger
     * @param string              $productCacheTag
     * @param array               $cacheTypes
     */
    public function __construct(
        FrontendPool $frontendPool,
        EventManager $eventManager,
        CacheContextFactory $cacheContextFactory,
        LoggerInterface $logger,
        string $productCacheTag,
        array $cacheTypes
    ) {
        $this->frontendPool = $frontendPool;
        $this->eventManager = $eventManager;
        $this->productCacheTag = $productCacheTag;
        $this->cacheContextFactory = $cacheContextFactory;
        $this->logger = $logger;
        $this->cacheTypes = $cacheTypes;
    }

    /**
     * @param array $skus
     */
    public function clean(array $productIds): void
    {
        if ($productIds) {
            $tags = $this->getTags($productIds);
            foreach ($this->cacheTypes as $cacheType) {
                $this->frontendPool->get($cacheType)->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                    $tags
                );
            }
            $cacheContext = $this->cacheContextFactory->create();
            $cacheContext->registerEntities($this->productCacheTag, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
            $this->logger->info('Release product cache cleaned', $productIds);
        }
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getTags(array $productIds): array
    {
        return array_map(function ($productId) {
            return $this->productCacheTag . '_' . $productId;
        }, $productIds);
    }
}
