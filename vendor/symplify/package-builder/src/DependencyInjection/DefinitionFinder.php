<?php

declare (strict_types=1);
namespace ConfigTransformer202107101\Symplify\PackageBuilder\DependencyInjection;

use ConfigTransformer202107101\Symfony\Component\DependencyInjection\ContainerBuilder;
use ConfigTransformer202107101\Symfony\Component\DependencyInjection\Definition;
use ConfigTransformer202107101\Symplify\PackageBuilder\Exception\DependencyInjection\DefinitionForTypeNotFoundException;
use Throwable;
/**
 * @see \Symplify\PackageBuilder\Tests\DependencyInjection\DefinitionFinderTest
 */
final class DefinitionFinder
{
    /**
     * @return Definition[]
     */
    public function findAllByType(\ConfigTransformer202107101\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $type) : array
    {
        $definitions = [];
        $containerBuilderDefinitions = $containerBuilder->getDefinitions();
        foreach ($containerBuilderDefinitions as $name => $definition) {
            $class = $definition->getClass() ?: $name;
            if (!$this->doesClassExists($class)) {
                continue;
            }
            if (\is_a($class, $type, \true)) {
                $definitions[$name] = $definition;
            }
        }
        return $definitions;
    }
    public function getByType(\ConfigTransformer202107101\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $type) : \ConfigTransformer202107101\Symfony\Component\DependencyInjection\Definition
    {
        $definition = $this->getByTypeIfExists($containerBuilder, $type);
        if ($definition !== null) {
            return $definition;
        }
        throw new \ConfigTransformer202107101\Symplify\PackageBuilder\Exception\DependencyInjection\DefinitionForTypeNotFoundException(\sprintf('Definition for type "%s" was not found.', $type));
    }
    private function getByTypeIfExists(\ConfigTransformer202107101\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder, string $type) : ?\ConfigTransformer202107101\Symfony\Component\DependencyInjection\Definition
    {
        $containerBuilderDefinitions = $containerBuilder->getDefinitions();
        foreach ($containerBuilderDefinitions as $name => $definition) {
            $class = $definition->getClass() ?: $name;
            if (!$this->doesClassExists($class)) {
                continue;
            }
            if (\is_a($class, $type, \true)) {
                return $definition;
            }
        }
        return null;
    }
    private function doesClassExists(string $class) : bool
    {
        try {
            return \class_exists($class);
        } catch (\Throwable $exception) {
            return \false;
        }
    }
}