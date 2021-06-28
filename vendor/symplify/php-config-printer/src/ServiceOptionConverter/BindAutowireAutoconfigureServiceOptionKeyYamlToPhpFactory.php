<?php

declare (strict_types=1);
namespace ConfigTransformer202106282\Symplify\PhpConfigPrinter\ServiceOptionConverter;

use ConfigTransformer202106282\PhpParser\Node\Arg;
use ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\Contract\Converter\ServiceOptionsKeyYamlToPhpFactoryInterface;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\ServiceOptionAnalyzer\ServiceOptionAnalyzer;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey;
use ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlServiceKey;
final class BindAutowireAutoconfigureServiceOptionKeyYamlToPhpFactory implements \ConfigTransformer202106282\Symplify\PhpConfigPrinter\Contract\Converter\ServiceOptionsKeyYamlToPhpFactoryInterface
{
    /**
     * @var \Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory
     */
    private $commonNodeFactory;
    /**
     * @var \Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory
     */
    private $argsNodeFactory;
    /**
     * @var \Symplify\PhpConfigPrinter\ServiceOptionAnalyzer\ServiceOptionAnalyzer
     */
    private $serviceOptionAnalyzer;
    public function __construct(\ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\CommonNodeFactory $commonNodeFactory, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory $argsNodeFactory, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ServiceOptionAnalyzer\ServiceOptionAnalyzer $serviceOptionAnalyzer)
    {
        $this->commonNodeFactory = $commonNodeFactory;
        $this->argsNodeFactory = $argsNodeFactory;
        $this->serviceOptionAnalyzer = $serviceOptionAnalyzer;
    }
    public function decorateServiceMethodCall($key, $yaml, $values, \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall $methodCall) : \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall
    {
        $method = $key;
        if ($key === 'shared') {
            $method = 'share';
        }
        if ($yaml === \false) {
            $methodCall = new \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall($methodCall, $method);
            $methodCall->args[] = new \ConfigTransformer202106282\PhpParser\Node\Arg($this->commonNodeFactory->createFalse());
            return $methodCall;
        }
        if (!$this->serviceOptionAnalyzer->hasNamedArguments($yaml)) {
            $args = $this->argsNodeFactory->createFromValuesAndWrapInArray($yaml);
            return new \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall($methodCall, 'bind', $args);
        }
        foreach ($yaml as $key => $value) {
            $args = $this->argsNodeFactory->createFromValues([$key, $value], \false, \true);
            $methodCall = new \ConfigTransformer202106282\PhpParser\Node\Expr\MethodCall($methodCall, 'bind', $args);
        }
        return $methodCall;
    }
    public function isMatch($key, $values) : bool
    {
        return \in_array($key, [\ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlServiceKey::BIND, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::AUTOWIRE, \ConfigTransformer202106282\Symplify\PhpConfigPrinter\ValueObject\YamlKey::AUTOCONFIGURE], \true);
    }
}