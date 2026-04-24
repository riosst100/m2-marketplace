<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;
use function sprintf;

/**
 * @api
 */
final class RendererFactory
{
    /**
     * @var string[]
     */
    private $renderers;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param string[] $renderers
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        array $renderers,
        ObjectManagerInterface $objectManager
    ) {
        $this->renderers = $renderers;
        $this->objectManager = $objectManager;
    }

    public function get(string $rendererCode): RendererInterface
    {
        if (!isset($this->renderers[$rendererCode])) {
            throw new InvalidArgumentException(sprintf('Unknown renderer type "%s".', $rendererCode));
        }

        return $this->objectManager->get($this->renderers[$rendererCode]);
    }
}
