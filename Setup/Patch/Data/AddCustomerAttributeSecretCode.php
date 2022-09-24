<?php
/**
 * Patch to add customer attribute secret_code
 *
 * @copyright Copyright (C) 2022
 * @author    Anton Smolenov
 */

declare(strict_types=1);

namespace AntonSmolenov\SecretCode\Setup\Patch\Data;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddCustomerAttributeSecretCode
 */
class AddCustomerAttributeSecretCode implements DataPatchInterface
{
    /**
     * Secret code attribute
     */
    const SECRET_CODE = 'secret_code';

    /**
     * Secret code attribute label
     */
    const SECRET_CODE_LABEL = 'Secret Code';

    /**
     * Dependencies
     */
    const DEPENDENCIES = [];

    /**
     * Aliases
     */
    const ALIASES = [];

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AddCustomerAttributeSecretCode constructor.
     *
     * @param EavSetupFactory          $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetFactory      $attributeSetFactory
     * @param Config                   $eavConfig
     * @param LoggerInterface          $logger
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory,
        Config $eavConfig,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->logger = $logger;
    }

    /**
     * Adds customer attribute secret_code
     */
    public function apply(): void
    {
        try {
            $customerEntity = $this->eavConfig->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $eavSetup->addAttribute(
                Customer::ENTITY,
                static::SECRET_CODE,
                [
                    'type' => 'varchar',
                    'label' => self::SECRET_CODE_LABEL,
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => true,
                    'position' => 100,
                    'system' => 0,
                ]
            );
            $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, static::SECRET_CODE);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $attribute->save();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Dependencies
     *
     * @return mixed[]
     */
    public static function getDependencies(): array
    {
        return static::DEPENDENCIES;
    }

    /**
     * Get Aliases
     *
     * @return mixed[]
     */
    public function getAliases(): array
    {
        return static::ALIASES;
    }
}
