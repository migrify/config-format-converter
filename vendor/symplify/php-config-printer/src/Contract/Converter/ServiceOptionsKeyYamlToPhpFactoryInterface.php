<?php

declare (strict_types=1);
namespace ConfigTransformer202107154\Symplify\PhpConfigPrinter\Contract\Converter;

use ConfigTransformer202107154\PhpParser\Node\Expr\MethodCall;
interface ServiceOptionsKeyYamlToPhpFactoryInterface
{
    /**
     * @param mixed $key
     * @param mixed $yaml
     * @param mixed $values
     * @param \PhpParser\Node\Expr\MethodCall $serviceMethodCall
     */
    public function decorateServiceMethodCall($key, $yaml, $values, $serviceMethodCall) : \ConfigTransformer202107154\PhpParser\Node\Expr\MethodCall;
    public function isMatch($key, $values) : bool;
}
