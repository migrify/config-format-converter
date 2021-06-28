<?php

declare (strict_types=1);
namespace ConfigTransformer202106287\Symplify\Astral\ValueObject\NodeBuilder;

use ConfigTransformer202106287\PhpParser\Builder\Use_;
use ConfigTransformer202106287\PhpParser\Node\Stmt\Use_ as UseStmt;
/**
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class UseBuilder extends \ConfigTransformer202106287\PhpParser\Builder\Use_
{
    public function __construct($name, int $type = \ConfigTransformer202106287\PhpParser\Node\Stmt\Use_::TYPE_NORMAL)
    {
        parent::__construct($name, $type);
    }
}
