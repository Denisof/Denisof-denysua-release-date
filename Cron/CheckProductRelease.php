<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Cron;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class CheckProductRelease
{
    const DATE_TIME_FORMAT = '%Y-%m-%d %H:%i:%s';

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $configEav;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \DenysUA\ReleaseDate\Model\CacheCleaner
     */
    private $cacheCleaner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CheckProductRelease constructor.
     *
     * @param \Magento\Eav\Model\Config                 $configEav
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \DenysUA\ReleaseDate\Model\CacheCleaner   $cacheCleaner
     * @param LoggerInterface                           $logger
     */
    public function __construct(
        \Magento\Eav\Model\Config $configEav,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \DenysUA\ReleaseDate\Model\CacheCleaner $cacheCleaner,
        LoggerInterface $logger
    ) {
        $this->configEav = $configEav;
        $this->resourceConnection = $resourceConnection;
        $this->cacheCleaner = $cacheCleaner;
        $this->logger = $logger;
    }

    /**
     *
     */
    public function execute(): void
    {
        try {
            $attribute = $this->configEav->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                \DenysUA\ReleaseDate\Model\ProductReleaseService::RELEASE_DAY_ATTRIBUTE_CODE
            );
        } catch (LocalizedException $e) {
            $this->logger->error(
                sprintf('Error during fetching Release date attribute %s', $e->getMessage())
            );

            return;
        }
        if (!$attribute) {
            $this->logger->error('Release date attribute not found');

            return;
        }

        try {
            $attributeId = $attribute->getAttributeId();
            $dateTime = new \DateTime('now');
            $backTime = new \DateTime('now');
            $backTime->sub(new \DateInterval('PT5M'));
            $backEndTable = $this->resourceConnection->getTableName($attribute->getBackendTable());
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select();
            $select->from(
                $backEndTable,
                $attribute->getEntityIdField()
            )->where(
                'attribute_id = ?',
                $attributeId
            )->where(
                $this->prepareBetweenSql(
                    'value',
                    $backTime->format('Y-m-d H:i:s'),
                    $dateTime->format('Y-m-d H:i:s'),

                )
            );
            $result = $connection->fetchCol($select);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Error occured during fertching release candidate products %s', $e->getMessage())
            );

            return;
        }
        if ($result) {
            $this->cacheCleaner->clean($result);
        }
    }

    /**
     * @param $fieldName
     * @param $from
     * @param $to
     *
     * @return string
     */
    private function prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            "(%s BETWEEN STR_TO_DATE(%s, '%s') AND STR_TO_DATE(%s, '%s'))",
            $fieldName,
            $this->resourceConnection->getConnection()->quote($from),
            self::DATE_TIME_FORMAT,
            $this->resourceConnection->getConnection()->quote($to),
            self::DATE_TIME_FORMAT,
        );
    }
}
