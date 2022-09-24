<?php
/**
 * Check secret_code plugin
 *
 * @copyright Copyright (C) 2022
 * @author    Anton Smolenov
 */

declare(strict_types=1);

namespace AntonSmolenov\SecretCode\Plugin;

use Exception;
use Magento\Customer\Model\CustomerRegistry;
use Magento\CustomerGraphQl\Model\Resolver\GenerateCustomerToken;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

/**
 * Class SecretCode
 */
class SecretCode
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SecretCode constructor.
     *
     * @param CustomerRegistry $customerRegistry
     * @param LoggerInterface  $logger
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        LoggerInterface $logger
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->logger = $logger;
    }

    /**
     * Before resolve() plugin
     *
     * @param GenerateCustomerToken $subject
     * @param Field                 $field
     * @param ContextInterface      $context
     * @param ResolveInfo           $info
     * @param array|null            $value
     * @param array|null            $args
     *
     * @return null
     * @throws GraphQlAuthenticationException
     * @throws GraphQlInputException
     */
    public function beforeResolve(
        GenerateCustomerToken $subject,
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['secret_code'])) {
            throw new GraphQlInputException(__('Specify the "secret_code" value.'));
        }
        try {
            $customer = $this->customerRegistry->retrieveByEmail($args['email']);
        } catch (NoSuchEntityException $e) {
            // If the user is not found, no action is required. An exception will be thrown in the native method.
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if (isset($customer) && $args['secret_code'] !== $customer->getSecretCode()) {
            throw new GraphQlAuthenticationException(__("Secret code is invalid."));
        }

        return null;
    }
}
