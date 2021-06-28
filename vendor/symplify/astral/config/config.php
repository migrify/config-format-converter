<?php

declare (strict_types=1);
namespace ConfigTransformer202106282;

use ConfigTransformer202106282\PhpParser\ConstExprEvaluator;
use ConfigTransformer202106282\PhpParser\NodeFinder;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ConfigTransformer202106282\Symplify\PackageBuilder\Php\TypeChecker;
return static function (\ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->autowire()->autoconfigure()->public();
    $services->load('ConfigTransformer202106282\Symplify\Astral\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/StaticFactory', __DIR__ . '/../src/ValueObject']);
    $services->set(\ConfigTransformer202106282\PhpParser\ConstExprEvaluator::class);
    $services->set(\ConfigTransformer202106282\Symplify\PackageBuilder\Php\TypeChecker::class);
    $services->set(\ConfigTransformer202106282\PhpParser\NodeFinder::class);
};