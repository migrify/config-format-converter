<?php

declare (strict_types=1);
namespace ConfigTransformer202107154\PhpParser\Builder;

use ConfigTransformer202107154\PhpParser\BuilderHelpers;
use ConfigTransformer202107154\PhpParser\Node;
abstract class FunctionLike extends \ConfigTransformer202107154\PhpParser\Builder\Declaration
{
    protected $returnByRef = \false;
    protected $params = [];
    /** @var string|Node\Name|Node\NullableType|null */
    protected $returnType = null;
    /**
     * Make the function return by reference.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeReturnByRef()
    {
        $this->returnByRef = \true;
        return $this;
    }
    /**
     * Adds a parameter.
     *
     * @param Node\Param|Param $param The parameter to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addParam($param)
    {
        $param = \ConfigTransformer202107154\PhpParser\BuilderHelpers::normalizeNode($param);
        if (!$param instanceof \ConfigTransformer202107154\PhpParser\Node\Param) {
            throw new \LogicException(\sprintf('Expected parameter node, got "%s"', $param->getType()));
        }
        $this->params[] = $param;
        return $this;
    }
    /**
     * Adds multiple parameters.
     *
     * @param array $params The parameters to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addParams($params)
    {
        foreach ($params as $param) {
            $this->addParam($param);
        }
        return $this;
    }
    /**
     * Sets the return type for PHP 7.
     *
     * @param string|Node\Name|Node\NullableType $type One of array, callable, string, int, float,
     *                                                 bool, iterable, or a class/interface name.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setReturnType($type)
    {
        $this->returnType = \ConfigTransformer202107154\PhpParser\BuilderHelpers::normalizeType($type);
        return $this;
    }
}
