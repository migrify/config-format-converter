<?php

declare (strict_types=1);
namespace ConfigTransformer202106282;

use ConfigTransformer202106282\Symfony\Component\Console\Application;
use ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ConfigTransformer202106282\Symplify\EasyTesting\Console\EasyTestingConsoleApplication;
use ConfigTransformer202106282\Symplify\PackageBuilder\Console\Command\CommandNaming;
return static function (\ConfigTransformer202106282\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('ConfigTransformer202106282\Symplify\EasyTesting\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/DataProvider', __DIR__ . '/../src/HttpKernel', __DIR__ . '/../src/ValueObject']);
    // console
    $services->set(\ConfigTransformer202106282\Symplify\EasyTesting\Console\EasyTestingConsoleApplication::class);
    $services->alias(\ConfigTransformer202106282\Symfony\Component\Console\Application::class, \ConfigTransformer202106282\Symplify\EasyTesting\Console\EasyTestingConsoleApplication::class);
    $services->set(\ConfigTransformer202106282\Symplify\PackageBuilder\Console\Command\CommandNaming::class);
};