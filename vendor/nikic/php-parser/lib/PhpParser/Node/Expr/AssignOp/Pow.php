<?php

declare (strict_types=1);
namespace ConfigTransformer202107037\PhpParser\Node\Expr\AssignOp;

use ConfigTransformer202107037\PhpParser\Node\Expr\AssignOp;
class Pow extends \ConfigTransformer202107037\PhpParser\Node\Expr\AssignOp
{
    public function getType() : string
    {
        return 'Expr_AssignOp_Pow';
    }
}
