<?php

declare (strict_types=1);
namespace ConfigTransformer202107154;

use ConfigTransformer202107154\Symplify\ConfigTransformer\HttpKernel\ConfigTransformerKernel;
use ConfigTransformer202107154\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun;
$possibleAutoloadPaths = [
    // when using `vendor/bin/config-transformer` from project root that depends on this package
    \getcwd() . '/vendor/autoload.php',
    // after split package
    __DIR__ . '/../vendor/autoload.php',
    // dependency
    __DIR__ . '/../../../autoload.php',
    // monorepo
    __DIR__ . '/../../../vendor/autoload.php',
];
foreach ($possibleAutoloadPaths as $possibleAutoloadPath) {
    if (\file_exists($possibleAutoloadPath)) {
        require_once $possibleAutoloadPath;
        break;
    }
}
$scoperAutoloadFilepath = __DIR__ . '/../vendor/scoper-autoload.php';
if (\file_exists($scoperAutoloadFilepath)) {
    require_once $scoperAutoloadFilepath;
}
$codeSnifferAutoload = \getcwd() . '/vendor/squizlabs/php_codesniffer/autoload.php';
if (\file_exists($codeSnifferAutoload)) {
    require_once $codeSnifferAutoload;
}
$kernelBootAndApplicationRun = new \ConfigTransformer202107154\Symplify\SymplifyKernel\ValueObject\KernelBootAndApplicationRun(\ConfigTransformer202107154\Symplify\ConfigTransformer\HttpKernel\ConfigTransformerKernel::class);
$kernelBootAndApplicationRun->run();
