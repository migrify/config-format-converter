<?php

declare (strict_types=1);
namespace ConfigTransformer2021061210\Symplify\ComposerJsonManipulator\DependencyInjection\Extension;

use ConfigTransformer2021061210\Symfony\Component\Config\FileLocator;
use ConfigTransformer2021061210\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer2021061210\Symfony\Component\DependencyInjection\Extension\Extension;
use ConfigTransformer2021061210\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
final class ComposerJsonManipulatorExtension extends \ConfigTransformer2021061210\Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, \ConfigTransformer2021061210\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $phpFileLoader = new \ConfigTransformer2021061210\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($containerBuilder, new \ConfigTransformer2021061210\Symfony\Component\Config\FileLocator(__DIR__ . '/../../../config'));
        $phpFileLoader->load('config.php');
    }
}
