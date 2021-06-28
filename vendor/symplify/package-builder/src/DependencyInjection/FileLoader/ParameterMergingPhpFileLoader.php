<?php

declare (strict_types=1);
namespace ConfigTransformer202106282\Symplify\PackageBuilder\DependencyInjection\FileLoader;

use ConfigTransformer202106282\Symfony\Component\Config\FileLocatorInterface;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use ConfigTransformer202106282\Symplify\PackageBuilder\Yaml\ParametersMerger;
/**
 * The need:
 * - https://github.com/symfony/symfony/issues/26713
 * - https://github.com/symfony/symfony/pull/21313#issuecomment-372037445
 */
final class ParameterMergingPhpFileLoader extends \ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\PhpFileLoader
{
    /**
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    public function __construct(\ConfigTransformer202106282\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, \ConfigTransformer202106282\Symfony\Component\Config\FileLocatorInterface $fileLocator)
    {
        $this->parametersMerger = new \ConfigTransformer202106282\Symplify\PackageBuilder\Yaml\ParametersMerger();
        parent::__construct($containerBuilder, $fileLocator);
    }
    /**
     * Same as parent, just merging parameters instead overriding them
     *
     * @see https://github.com/symplify/symplify/pull/697
     */
    public function load($resource, string $type = null) : void
    {
        // get old parameters
        $parameterBag = $this->container->getParameterBag();
        $oldParameters = $parameterBag->all();
        parent::load($resource);
        foreach ($oldParameters as $key => $oldValue) {
            $newValue = $this->parametersMerger->merge($oldValue, $this->container->getParameter($key));
            $this->container->setParameter($key, $newValue);
        }
    }
}