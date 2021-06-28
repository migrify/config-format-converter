<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202106282\Symfony\Component\HttpKernel\DependencyInjection;

use ConfigTransformer202106282\Psr\Log\LoggerInterface;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202106282\Symfony\Component\HttpKernel\Log\Logger;
/**
 * Registers the default logger if necessary.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class LoggerPass implements \ConfigTransformer202106282\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(\ConfigTransformer202106282\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $container->setAlias(\ConfigTransformer202106282\Psr\Log\LoggerInterface::class, 'logger')->setPublic(\false);
        if ($container->has('logger')) {
            return;
        }
        $container->register('logger', \ConfigTransformer202106282\Symfony\Component\HttpKernel\Log\Logger::class)->setPublic(\false);
    }
}