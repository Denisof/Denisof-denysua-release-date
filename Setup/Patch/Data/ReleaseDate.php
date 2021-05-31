<?php
declare(strict_types = 1);

namespace DenysUA\ReleaseDate\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchInterface;

class ReleaseDate implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    const RELEASE_DAY_ATTRIBUTE_CODE = 'release_date_time';
    /**
     * @param EavSetupFactory          $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
       return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::RELEASE_DAY_ATTRIBUTE_CODE,
            [
                'type' => 'datetime',
                'label' => 'Release DateTime',
                'input' => 'datetime',
                'backend' => Datetime::class,
                'frontend_class' => 'validate-optional-datetime',
                'apply_to' => 'simple',
                'required' => false,
                'sort_order' => 99,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]

        );

        $attributeSetIds = $eavSetup->getAllAttributeSetIds(Product::ENTITY);

        foreach ($attributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSetId,
                $groupId,
                self::RELEASE_DAY_ATTRIBUTE_CODE
            );
        }
    }
}
