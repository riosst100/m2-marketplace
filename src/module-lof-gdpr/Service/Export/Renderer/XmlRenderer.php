<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Export\Renderer;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

final class XmlRenderer extends AbstractRenderer
{
    private const ROOT_NAME = 'data';

    /**
     * @var ConvertArray
     */
    private $convertArray;

    /**
     * @var string
     */
    private $rootName;

    public function __construct(
        Filesystem $filesystem,
        ConvertArray $convertArray,
        string $rootName = self::ROOT_NAME
    ) {
        $this->convertArray = $convertArray;
        $this->rootName = $rootName;
        parent::__construct($filesystem, 'xml');
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function render(array $data): string
    {
        return $this->convertArray->assocToXml($data, $this->rootName)->saveXML();
    }
}
