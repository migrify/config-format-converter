<?php

declare (strict_types=1);
namespace ConfigTransformer202107154\Symplify\Astral\NodeNameResolver;

use ConfigTransformer202107154\PhpParser\Node;
use ConfigTransformer202107154\PhpParser\Node\Expr;
use ConfigTransformer202107154\PhpParser\Node\Expr\FuncCall;
use ConfigTransformer202107154\Symplify\Astral\Contract\NodeNameResolverInterface;
final class FuncCallNodeNameResolver implements \ConfigTransformer202107154\Symplify\Astral\Contract\NodeNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool
    {
        return $node instanceof \ConfigTransformer202107154\PhpParser\Node\Expr\FuncCall;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string
    {
        if ($node->name instanceof \ConfigTransformer202107154\PhpParser\Node\Expr) {
            return null;
        }
        return (string) $node->name;
    }
}
