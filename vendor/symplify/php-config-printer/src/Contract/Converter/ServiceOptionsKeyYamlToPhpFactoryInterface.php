<?php

declare (strict_types=1);
namespace ConfigTransformer202106282\Symplify\PhpConfigPrinter\Contract\Converter;

use ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall;
interface ServiceOptionsKeyYamlToPhpFactoryInterface
{
    /**
     * @param mixed $key
     * @param mixed $yaml
     * @param mixed $values
     */
    public function decorateServiceMethodCall($key, $yaml, $values, \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall $serviceMethodCall) : \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall;
    public function isMatch($key, $values) : bool;
}