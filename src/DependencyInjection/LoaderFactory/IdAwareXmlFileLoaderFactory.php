<?php

declare (strict_types=1);
namespace ConfigTransformer2021061110\Symplify\ConfigTransformer\DependencyInjection\LoaderFactory;

use ConfigTransformer2021061110\Symfony\Component\Config\FileLocator;
use ConfigTransformer2021061110\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer2021061110\Symplify\ConfigTransformer\Collector\XmlImportCollector;
use ConfigTransformer2021061110\Symplify\ConfigTransformer\Configuration\Configuration;
use ConfigTransformer2021061110\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader;
use ConfigTransformer2021061110\Symplify\ConfigTransformer\Naming\UniqueNaming;
final class IdAwareXmlFileLoaderFactory
{
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var UniqueNaming
     */
    private $uniqueNaming;
    /**
     * @var XmlImportCollector
     */
    private $xmlImportCollector;
    public function __construct(\ConfigTransformer2021061110\Symplify\ConfigTransformer\Configuration\Configuration $configuration, \ConfigTransformer2021061110\Symplify\ConfigTransformer\Naming\UniqueNaming $uniqueNaming, \ConfigTransformer2021061110\Symplify\ConfigTransformer\Collector\XmlImportCollector $xmlImportCollector)
    {
        $this->configuration = $configuration;
        $this->uniqueNaming = $uniqueNaming;
        $this->xmlImportCollector = $xmlImportCollector;
    }
    public function createFromContainerBuilder(\ConfigTransformer2021061110\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : \ConfigTransformer2021061110\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader
    {
        return new \ConfigTransformer2021061110\Symplify\ConfigTransformer\DependencyInjection\Loader\IdAwareXmlFileLoader($containerBuilder, new \ConfigTransformer2021061110\Symfony\Component\Config\FileLocator(), $this->configuration, $this->uniqueNaming, $this->xmlImportCollector);
    }
}
