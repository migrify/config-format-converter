<?php

declare (strict_types=1);
namespace ConfigTransformer202106187\Symplify\PhpConfigPrinter\Dummy;

use ConfigTransformer202106187\Symplify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface;
final class DummySymfonyVersionFeatureGuard implements \ConfigTransformer202106187\Symplify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface
{
    public function isAtLeastSymfonyVersion(float $symfonyVersion) : bool
    {
        return \true;
    }
}
