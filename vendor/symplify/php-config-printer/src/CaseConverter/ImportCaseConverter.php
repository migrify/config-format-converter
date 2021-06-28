<?php

declare (strict_types=1);
namespace ConfigTransformer202106282\Symplify\PhpConfigPrinter\CaseConverter;

use ConfigTransformer202106282\Nette\Utils\Strings;
use ConfigTransformer202106282\PhpParser\BuilderHelpers;
use ConfigTransformer202106282\PhpParser\Node\Arg;
use ConfigTransformer202106282\PhpParser\Node\Expr;
use ConfigTransformer202106282\PhpParser\Node\Expr\ClassConstFetch;
use ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall;
use ConfigTransformer202106282\PhpParser\Node\Expr\Variable;
use ConfigTransformer202106282\PhpParser\Node\Name\FullyQualified;
use ConfigTransformer202106282\PhpParser\Node\Scalar\String_;
use ConfigTransformer202106282\PhpParser\Node\Stmt\Expression;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\Contract\CaseConverterInterface;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\Exception\NotImplementedYetException;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\Sorter\YamlArgumentSorter;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\VariableName;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey;
/**
 * Handles this part:
 *
 * imports: <---
 */
final class ImportCaseConverter implements \ConfigTransformer202106282\Symplify\PhpConfigPrinter\Contract\CaseConverterInterface
{
    /**
     * @see https://regex101.com/r/hOTdIE/1
     * @var string
     */
    private const INPUT_SUFFIX_REGEX = '#\\.(yml|yaml|xml)$#';
    /**
     * @var \Symplify\PhpConfigPrinter\Sorter\YamlArgumentSorter
     */
    private $yamlArgumentSorter;
    /**
     * @var \Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory
     */
    private $commonNodeFactory;
    public function __construct(\ConfigTransformer202106282\Symplify\PhpConfigPrinter\Sorter\YamlArgumentSorter $yamlArgumentSorter, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory $commonNodeFactory)
    {
        $this->yamlArgumentSorter = $yamlArgumentSorter;
        $this->commonNodeFactory = $commonNodeFactory;
    }
    public function match(string $rootKey, $key, $values) : bool
    {
        return $rootKey === \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::IMPORTS;
    }
    /**
     * @param mixed|mixed[] $values
     */
    public function convertToMethodCall($key, $values) : \ConfigTransformer202106282\PhpParser\Node\Stmt\Expression
    {
        if (\is_array($values)) {
            $arguments = $this->yamlArgumentSorter->sortArgumentsByKeyIfExists($values, [\ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::RESOURCE => '', 'type' => null, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::IGNORE_ERRORS => \false]);
            return $this->createImportMethodCall($arguments);
        }
        if (\is_string($values)) {
            return $this->createImportMethodCall([$values]);
        }
        throw new \ConfigTransformer202106282\Symplify\PhpConfigPrinter\Exception\NotImplementedYetException();
    }
    /**
     * @param mixed[] $arguments
     */
    private function createImportMethodCall(array $arguments) : \ConfigTransformer202106282\PhpParser\Node\Stmt\Expression
    {
        $containerConfiguratorVariable = new \ConfigTransformer202106282\PhpParser\Node\Expr\Variable(\ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\VariableName::CONTAINER_CONFIGURATOR);
        $args = $this->createArgs($arguments);
        $methodCall = new \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall($containerConfiguratorVariable, 'import', $args);
        return new \ConfigTransformer202106282\PhpParser\Node\Stmt\Expression($methodCall);
    }
    /**
     * @param array<int|string, mixed> $arguments
     * @return Arg[]
     */
    private function createArgs(array $arguments) : array
    {
        $args = [];
        foreach ($arguments as $name => $value) {
            if (\is_string($name) && $this->shouldSkipDefaultValue($name, $value, $arguments)) {
                continue;
            }
            $expr = $this->resolveExpr($value);
            $args[] = new \ConfigTransformer202106282\PhpParser\Node\Arg($expr);
        }
        return $args;
    }
    private function shouldSkipDefaultValue(string $name, $value, array $arguments) : bool
    {
        // skip default value for "ignore_errors"
        if ($name === \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::IGNORE_ERRORS && $value === \false) {
            return \true;
        }
        // check if default value for "type"
        if ($name !== 'type') {
            return \false;
        }
        if ($value !== null) {
            return \false;
        }
        // follow by default value for "ignore_errors"
        if (!isset($arguments[\ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::IGNORE_ERRORS])) {
            return \false;
        }
        return $arguments[\ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::IGNORE_ERRORS] === \false;
    }
    /**
     * @return mixed|string
     */
    private function replaceImportedFileSuffix($value)
    {
        if (!\is_string($value)) {
            return $value;
        }
        return \ConfigTransformer202106282\Nette\Utils\Strings::replace($value, self::INPUT_SUFFIX_REGEX, '.php');
    }
    private function resolveExpr($value) : \ConfigTransformer202106282\PhpParser\Node\Expr
    {
        if (\is_bool($value)) {
            return \ConfigTransformer202106282\PhpParser\BuilderHelpers::normalizeValue($value);
        }
        if (\in_array($value, ['annotations', 'directory', 'glob'], \true)) {
            return \ConfigTransformer202106282\PhpParser\BuilderHelpers::normalizeValue($value);
        }
        if ($value === 'not_found') {
            return new \ConfigTransformer202106282\PhpParser\Node\Scalar\String_('not_found');
        }
        if (\is_string($value) && \strpos($value, '::') !== \false) {
            [$className, $constantName] = \explode('::', $value);
            return new \ConfigTransformer202106282\PhpParser\Node\Expr\ClassConstFetch(new \ConfigTransformer202106282\PhpParser\Node\Name\FullyQualified($className), $constantName);
        }
        $value = $this->replaceImportedFileSuffix($value);
        return $this->commonNodeFactory->createAbsoluteDirExpr($value);
    }
}