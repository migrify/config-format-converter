<?php

declare (strict_types=1);
namespace ConfigTransformer202107154\Symplify\Astral\NodeNameResolver;

use ConfigTransformer202107154\PhpParser\Node;
use ConfigTransformer202107154\PhpParser\Node\Expr;
use ConfigTransformer202107154\PhpParser\Node\Param;
use ConfigTransformer202107154\Symplify\Astral\Contract\NodeNameResolverInterface;
final class ParamNodeNameResolver implements \ConfigTransformer202107154\Symplify\Astral\Contract\NodeNameResolverInterface
{
    /**
     * @param \PhpParser\Node $node
     */
    public function match($node) : bool
    {
        return $node instanceof \ConfigTransformer202107154\PhpParser\Node\Param;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string
    {
        $paramName = $node->var->name;
        if ($paramName instanceof \ConfigTransformer202107154\PhpParser\Node\Expr) {
            return null;
        }
        return $paramName;
    }
}
