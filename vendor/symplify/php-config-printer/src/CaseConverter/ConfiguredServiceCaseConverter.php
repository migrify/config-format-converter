<?php

declare (strict_types=1);
namespace ConfigTransformer2021061210\Symplify\PhpConfigPrinter\CaseConverter;

use ConfigTransformer2021061210\Nette\Utils\Strings;
use ConfigTransformer2021061210\PhpParser\Node\Expr\MethodCall;
use ConfigTransformer2021061210\PhpParser\Node\Expr\Variable;
use ConfigTransformer2021061210\PhpParser\Node\Stmt\Expression;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\Contract\CaseConverterInterface;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\NodeFactory\Service\ServiceOptionNodeFactory;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\MethodName;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\VariableName;
use ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey;
final class ConfiguredServiceCaseConverter implements \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\Contract\CaseConverterInterface
{
    /**
     * @var ArgsNodeFactory
     */
    private $argsNodeFactory;
    /**
     * @var ServiceOptionNodeFactory
     */
    private $serviceOptionNodeFactory;
    public function __construct(\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory $argsNodeFactory, \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\NodeFactory\Service\ServiceOptionNodeFactory $serviceOptionNodeFactory)
    {
        $this->argsNodeFactory = $argsNodeFactory;
        $this->serviceOptionNodeFactory = $serviceOptionNodeFactory;
    }
    public function convertToMethodCall($key, $values) : \ConfigTransformer2021061210\PhpParser\Node\Stmt\Expression
    {
        $valuesForArgs = [$key];
        if (isset($values[\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::CLASS_KEY])) {
            $valuesForArgs[] = $values[\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::CLASS_KEY];
        }
        $args = $this->argsNodeFactory->createFromValues($valuesForArgs);
        $methodCall = new \ConfigTransformer2021061210\PhpParser\Node\Expr\MethodCall(new \ConfigTransformer2021061210\PhpParser\Node\Expr\Variable(\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\VariableName::SERVICES), \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\MethodName::SET, $args);
        $methodCall = $this->serviceOptionNodeFactory->convertServiceOptionsToNodes($values, $methodCall);
        return new \ConfigTransformer2021061210\PhpParser\Node\Stmt\Expression($methodCall);
    }
    public function match(string $rootKey, $key, $values) : bool
    {
        if ($rootKey !== \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::SERVICES) {
            return \false;
        }
        if ($key === \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::_DEFAULTS) {
            return \false;
        }
        if ($key === \ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::_INSTANCEOF) {
            return \false;
        }
        if (isset($values[\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::RESOURCE])) {
            return \false;
        }
        // handled by @see \Symplify\PhpConfigPrinter\CaseConverter\CaseConverter\AliasCaseConverter
        if ($this->isAlias($values)) {
            return \false;
        }
        if ($values === null) {
            return \false;
        }
        return $values !== [];
    }
    private function isAlias($values) : bool
    {
        if (isset($values[\ConfigTransformer2021061210\Symplify\PhpConfigPrinter\ValueObject\YamlKey::ALIAS])) {
            return \true;
        }
        if (!\is_string($values)) {
            return \false;
        }
        return \ConfigTransformer2021061210\Nette\Utils\Strings::startsWith($values, '@');
    }
}
