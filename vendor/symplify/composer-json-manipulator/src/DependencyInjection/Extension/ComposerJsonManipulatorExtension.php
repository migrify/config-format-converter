<?php

declare (strict_types=1);
namespace ConfigTransformer202106202\Symplify\ComposerJsonManipulator\DependencyInjection\Extension;

use ConfigTransformer202106202\Symfony\Component\Config\FileLocator;
use ConfigTransformer202106202\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202106202\Symfony\Component\DependencyInjection\Extension\Extension;
use ConfigTransformer202106202\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class ComposerJsonManipulatorExtension extends \ConfigTransformer202106202\Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, \ConfigTransformer202106202\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $phpFileLoader = new \ConfigTransformer202106202\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \ConfigTransformer202106202\Symfony\Component\Config\FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('config.php');
    }
}
