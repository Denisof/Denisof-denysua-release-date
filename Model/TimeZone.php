<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class TimeZone
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * TimeZone constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \DateTimeZone
     */
    public function getCurrentTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone(
            $this->scopeConfig->getValue(
                \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_TIMEZONE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
        );
    }
}
