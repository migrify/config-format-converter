<?php

declare (strict_types=1);
namespace ConfigTransformer202106287\Symplify\ConfigTransformer\DependencyInjection\LoaderFactory;

use ConfigTransformer202106287\Symfony\Component\Config\FileLocator;
use ConfigTransformer202106287\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202106287\Symplify\ConfigTransformer\Collector\XmlImportCollector;
use ConfigTransformer202106287\Symplify\ConfigTransformer\Configuration\Configuration;
use ConfigTransformer202106287\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader;
use ConfigTransformer202106287\Symplify\ConfigTransformer\Naming\UniqueNaming;
final class IdAwareXmlFileLoaderFactory
{
    /**
     * @var \Symplify\ConfigTransformer\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Symplify\ConfigTransformer\Naming\UniqueNaming
     */
    private $uniqueNaming;
    /**
     * @var \Symplify\ConfigTransformer\Collector\XmlImportCollector
     */
    private $xmlImportCollector;
    public function __construct(\ConfigTransformer202106287\Symplify\ConfigTransformer\Configuration\Configuration $configuration, \ConfigTransformer202106287\Symplify\ConfigTransformer\Naming\UniqueNaming $uniqueNaming, \ConfigTransformer202106287\Symplify\ConfigTransformer\Collector\XmlImportCollector $xmlImportCollector)
    {
        $this->configuration = $configuration;
        $this->uniqueNaming = $uniqueNaming;
        $this->xmlImportCollector = $xmlImportCollector;
    }
    public function createFromContainerBuilder(\ConfigTransformer202106287\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : \ConfigTransformer202106287\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader
    {
        return new \ConfigTransformer202106287\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader($containerBuilder, new \ConfigTransformer202106287\Symfony\Component\Config\FileLocator(), $this->configuration, $this->uniqueNaming, $this->xmlImportCollector);
    }
}
