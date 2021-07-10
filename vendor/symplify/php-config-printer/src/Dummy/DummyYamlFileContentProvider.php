<?php

declare (strict_types=1);
namespace ConfigTransformer202107101\Symplify\PhpConfigPrinter\Dummy;

use ConfigTransformer202107101\Symplify\PhpConfigPrinter\Contract\YamlFileContentProviderInterface;
final class DummyYamlFileContentProvider implements \ConfigTransformer202107101\Symplify\PhpConfigPrinter\Contract\YamlFileContentProviderInterface
{
    /**
     * @param string $yamlContent
     */
    public function setContent($yamlContent) : void
    {
    }
    public function getYamlContent() : string
    {
        return '';
    }
}