<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer20210606\Symfony\Component\ExpressionLanguage\Node;

use ConfigTransformer20210606\Symfony\Component\ExpressionLanguage\Compiler;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
class ConstantNode extends \ConfigTransformer20210606\Symfony\Component\ExpressionLanguage\Node\Node
{
    private $isIdentifier;
    public function __construct($value, bool $isIdentifier = \false)
    {
        $this->isIdentifier = $isIdentifier;
        parent::__construct([], ['value' => $value]);
    }
    public function compile(\ConfigTransformer20210606\Symfony\Component\ExpressionLanguage\Compiler $compiler)
    {
        $compiler->repr($this->attributes['value']);
    }
    public function evaluate(array $functions, array $values)
    {
        return $this->attributes['value'];
    }
    public function toArray()
    {
        $array = [];
        $value = $this->attributes['value'];
        if ($this->isIdentifier) {
            $array[] = $value;
        } elseif (\true === $value) {
            $array[] = 'true';
        } elseif (\false === $value) {
            $array[] = 'false';
        } elseif (null === $value) {
            $array[] = 'null';
        } elseif (\is_numeric($value)) {
            $array[] = $value;
        } elseif (!\is_array($value)) {
            $array[] = $this->dumpString($value);
        } elseif ($this->isHash($value)) {
            foreach ($value as $k => $v) {
                $array[] = ', ';
                $array[] = new self($k);
                $array[] = ': ';
                $array[] = new self($v);
            }
            $array[0] = '{';
            $array[] = '}';
        } else {
            foreach ($value as $v) {
                $array[] = ', ';
                $array[] = new self($v);
            }
            $array[0] = '[';
            $array[] = ']';
        }
        return $array;
    }
}
