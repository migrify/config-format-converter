<?php

declare (strict_types=1);
namespace ConfigTransformer202107129\PhpParser\Node\Stmt;

use ConfigTransformer202107129\PhpParser\Node;
class Case_ extends \ConfigTransformer202107129\PhpParser\Node\Stmt
{
    /** @var null|Node\Expr Condition (null for default) */
    public $cond;
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /**
     * Constructs a case node.
     *
     * @param null|Node\Expr $cond       Condition (null for default)
     * @param Node\Stmt[]    $stmts      Statements
     * @param array          $attributes Additional attributes
     */
    public function __construct($cond, array $stmts = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames() : array
    {
        return ['cond', 'stmts'];
    }
    public function getType() : string
    {
        return 'Stmt_Case';
    }
}
