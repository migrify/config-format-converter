<?php

declare (strict_types=1);
namespace ConfigTransformer202107037\Symplify\PhpConfigPrinter\Contract;

use ConfigTransformer202107037\PhpParser\Node\Stmt\Expression;
interface NestedCaseConverterInterface
{
    public function match(string $rootKey, $subKey) : bool;
    public function convertToMethodCall($key, $values) : \ConfigTransformer202107037\PhpParser\Node\Stmt\Expression;
}
