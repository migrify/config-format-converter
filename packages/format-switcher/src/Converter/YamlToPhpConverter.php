<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\Converter;

use InvalidArgumentException;
use LogicException;
use Migrify\ConfigTransformer\FormatSwitcher\Exception\NotImplementedYetException;
use Migrify\ConfigTransformer\FormatSwitcher\Exception\ShouldNotHappenException;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\ArgsNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\ClosureNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\CommonNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\ImportNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\ParametersPhpNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\PhpNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\ServicesPhpNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\SingleServicePhpNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\Printer\FluentPhpConfigurationPrinter;
use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\VariableName;
use Nette\Utils\Strings;
use PhpParser\Builder\Use_ as UseBuilder;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Use_;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

/**
 * @source https://raw.githubusercontent.com/archeoprog/maker-bundle/make-convert-services/src/Util/PhpServicesCreator.php
 *
 * @see \Migrify\ConfigTransformer\FormatSwitcher\Tests\Converter\ConfigFormatConverter\YamlToPhpTest
 */
final class YamlToPhpConverter
{
    /**
     * @var string
     */
    private const INLINE_SERVICE = 'inline_service';

    /**
     * @var string
     */
    private const ARGUMENTS = 'arguments';

    /**
     * @var string
     */
    private const CALLS = 'calls';

    /**
     * @var string
     */
    private const TAGS = 'tags';

    /**
     * @var string
     */
    private const CONFIGURATOR = 'configurator';

    /**
     * @var string
     */
    private const FACTORY = 'factory';

    /**
     * @var string
     */
    private const EOL_CHAR = "\n";

    /**
     * @var string
     */
    private const DEFAULTS = '_defaults';

    /**
     * @var string
     */
    private const INSTANCE_OF = '_instanceof';

    /**
     * @var string
     */
    private const RESOURCE = 'resource';

    /**
     * @var string
     */
    private const CLASS_KEY = 'class';

    /**
     * @var string
     */
    private const TAG_SERVICE = 'service';

    /**
     * @var string
     */
    private const ALIAS = 'alias';

    /**
     * @var Node[]
     */
    private $stmts = [];

    /**
     * @var string[]
     */
    private $useStatements = [];

    /**
     * @var Parser
     */
    private $yamlParser;

    /**
     * @var PhpNodeFactory
     */
    private $phpNodeFactory;

    /**
     * @var ServicesPhpNodeFactory
     */
    private $servicesPhpNodeFactory;

    /**
     * @var ClosureNodeFactory
     */
    private $closureNodeFactory;

    /**
     * @var ParametersPhpNodeFactory
     */
    private $parametersPhpNodeFactory;

    /**
     * @var SingleServicePhpNodeFactory
     */
    private $singleServicePhpNodeFactory;

    /**
     * @var ImportNodeFactory
     */
    private $importNodeFactory;

    /**
     * @var CommonNodeFactory
     */
    private $commonNodeFactory;

    /**
     * @var ArgsNodeFactory
     */
    private $argsNodeFactory;

    /**
     * @var FluentPhpConfigurationPrinter
     */
    private $fluentPhpConfigurationPrinter;

    /**
     * @todo decopule to collector pattern, listen to key name
     */
    public function __construct(
        Parser $yamlParser,
        PhpNodeFactory $phpNodeFactory,
        ServicesPhpNodeFactory $servicesPhpNodeFactory,
        ClosureNodeFactory $closureNodeFactory,
        ParametersPhpNodeFactory $parametersPhpNodeFactory,
        FluentPhpConfigurationPrinter $fluentPhpConfigurationPrinter,
        SingleServicePhpNodeFactory $singleServicePhpNodeFactory,
        ImportNodeFactory $importNodeFactory,
        CommonNodeFactory $commonFactory,
        ArgsNodeFactory $argsNodeFactory
    ) {
        $this->yamlParser = $yamlParser;
        $this->phpNodeFactory = $phpNodeFactory;
        $this->servicesPhpNodeFactory = $servicesPhpNodeFactory;
        $this->closureNodeFactory = $closureNodeFactory;
        $this->parametersPhpNodeFactory = $parametersPhpNodeFactory;
        $this->singleServicePhpNodeFactory = $singleServicePhpNodeFactory;
        $this->importNodeFactory = $importNodeFactory;
        $this->commonNodeFactory = $commonFactory;
        $this->argsNodeFactory = $argsNodeFactory;
        $this->fluentPhpConfigurationPrinter = $fluentPhpConfigurationPrinter;
    }

    public function convert(string $yaml): string
    {
        $namespaceName = new Name('Symfony\Component\DependencyInjection\Loader\Configurator');
        $namespace = new Namespace_($namespaceName);

        $yamlArray = $this->yamlParser->parse($yaml, Yaml::PARSE_CUSTOM_TAGS);
        $closureStmts = $this->createClosureStmts($yamlArray, $namespace);

        $closure = $this->closureNodeFactory->createClosureFromStmts($closureStmts);
        $return = new Return_($closure);

        // add a blank line between the last use statement and the closure
        if ($this->useStatements !== []) {
            $namespace->stmts[] = new Nop();
        }

        $namespace->stmts[] = $return;

        return $this->fluentPhpConfigurationPrinter->prettyPrintFile([$namespace]);
    }

    public function createTaggedValue(TaggedValue $taggedValue): Node
    {
        if ($taggedValue->getTag() === self::TAG_SERVICE) {
            $class = $taggedValue->getValue()[self::CLASS_KEY];

            $classReference = $this->commonNodeFactory->createClassReference($class);
            $args = $this->argsNodeFactory->createFromValues([$classReference]);

            return new FuncCall(new Name('inline_service'), $args);
        }

        if (is_array($taggedValue->getValue())) {
            $argumentsInOrder = $this->sortArgumentsByKeyIfExists(
                $taggedValue->getValue(),
                ['tag', 'index_by', 'default_index_method']
            );

            $args = $this->argsNodeFactory->createFromValues($argumentsInOrder);
        } else {
            $args = $this->argsNodeFactory->createFromValues([$taggedValue->getValue()]);
        }

        return new FuncCall(new Name($taggedValue->getTag()), $args);
    }

    /**
     * @return Node[]
     */
    private function createClosureStmts(array $yamlData, Namespace_ $namespace): array
    {
        foreach ($yamlData as $key => $values) {
            if ($values === null) {
                // declare the variable ($parameters/$services) even if the key is written without values.
                $values = [];
            }

            switch ($key) {
                case 'parameters':
                    $this->addParametersNodes($values);
                    break;
                case 'imports':
                    $this->addImportsNodes($values);
                    break;
                case 'services':
                    $this->addServicesNodes($values);
                    break;
                default:
                    throw new LogicException(sprintf(
                        'The key %s is not supported by the converter: only service config can live in services.yaml for conversion.',
                        $key
                    ));
                    break;
            }
        }

        $this->addImportsToNamespace($namespace);
        $this->removeLastEmptyLine();

        return $this->stmts;
    }

    private function addParametersNodes(array $parameters): void
    {
        $initAssign = $this->parametersPhpNodeFactory->createParametersInit();
        $this->addNode($initAssign);

        foreach ($parameters as $parameterName => $value) {
            $methodCall = $this->phpNodeFactory->createParameterSetMethodCall($parameterName, $value);
            $this->addNode($methodCall);
        }

        // separater parameters by empty space
        $this->addNode(new Nop());
    }

    private function addImportsNodes(array $imports): void
    {
        foreach ($imports as $import) {
            if (is_array($import)) {
                $arguments = $this->sortArgumentsByKeyIfExists($import, [self::RESOURCE, 'type', 'ignore_errors']);

                $methodCall = $this->importNodeFactory->createImportMethodCall($arguments);
                $this->addNode($methodCall);
                continue;
            }

            throw new NotImplementedYetException();
        }

        $this->addNode(new Nop());
    }

    private function addServicesNodes(array $services): void
    {
        $assign = $this->servicesPhpNodeFactory->createServicesInit();
        $this->addNode($assign);

        $this->addEmptyLine();

        foreach ($services as $serviceKey => $serviceValues) {
            if ($serviceKey === self::DEFAULTS) {
                $defaults = $this->servicesPhpNodeFactory->createServiceDefaults($serviceValues);
                $this->addNode($defaults);
                $this->addEmptyLine();

                continue;
            }

            if ($serviceKey === self::INSTANCE_OF) {
                foreach ($serviceValues as $instanceKey => $instanceValues) {
                    $classReference = $this->commonNodeFactory->createClassReference($instanceKey);

                    $servicesVariable = new Variable(VariableName::SERVICES);
                    $args = [new Arg($classReference)];
                    $instanceofMethodCall = new MethodCall($servicesVariable, 'instanceof', $args);
                    $instanceofMethodCall = $this->convertServiceOptionsToNodes($instanceValues, $instanceofMethodCall);

                    $instanceofMethodCallExpression = new Expression($instanceofMethodCall);
                    $this->addNode($instanceofMethodCallExpression);

                    $this->addEmptyLine();
                }

                continue;
            }

            if (isset($serviceValues[self::RESOURCE])) {
                // Due to the yaml behavior that does not allow the declaration of several identical key names.
                if (isset($serviceValues['namespace'])) {
                    $serviceKey = $serviceValues['namespace'];
                    unset($serviceValues['namespace']);
                }

                $resource = $this->servicesPhpNodeFactory->createResource($serviceKey, $serviceValues);
                $this->addNode($resource);

                $this->addEmptyLine();

                continue;
            }

            if ($this->isAlias($serviceKey, $serviceValues)) {
                $this->addAliasNode($serviceKey, $serviceValues);
                continue;
            }

            if (isset($serviceValues[self::CLASS_KEY])) {
                $this->createService($serviceValues, $serviceKey);
                continue;
            }

            if ($serviceValues === null) {
                $setMethodCall = $this->singleServicePhpNodeFactory->createSetService($serviceKey);
            } else {
                $classReference = $this->commonNodeFactory->createClassReference($serviceKey);
                $setMethodCall = new MethodCall(new Variable(VariableName::SERVICES), 'set', [
                    new Arg($classReference),
                ]);

                $setMethodCall = $this->convertServiceOptionsToNodes($serviceValues, $setMethodCall);
            }

            $setMethodCallExpression = new Expression($setMethodCall);
            $this->addNode($setMethodCallExpression);
            $this->addEmptyLine();
        }
    }

    private function convertServiceOptionsToNodes(array $servicesValues, MethodCall $methodCall): MethodCall
    {
        foreach ($servicesValues as $serviceConfigKey => $value) {
            // options started by decoration_<option> are used as options of the method decorate().
            if (strstr($serviceConfigKey, 'decoration_')) {
                continue;
            }

            switch ($serviceConfigKey) {
                case 'decorates':
                    if ($methodCall === null) {
                        throw new ShouldNotHappenException();
                    }

                    $methodCall = $this->createDecorateMethod($servicesValues, $methodCall);
                    break;

                case 'deprecated':
                    $methodCall = $this->createDeprecateMethod($value, $methodCall);
                    break;

                // simple "key: value" options
                case 'shared':
                case 'public':
                    if ($methodCall !== null) {
                        if ($serviceConfigKey === 'public' && $value === false) {
                            $methodCall = new MethodCall($methodCall, 'private');
                            break;
                        }
                    }

                    if (is_array($value)) {
                        $this->addLineStmt($this->createMethod(
                            $serviceConfigKey,
                            // the handles converting all formats of the single arg
                            $this->toString($value)
                        ));

                        break;
                    }
                    // no break
                case 'bind':
                case 'autowire':
                case 'autoconfigure':
                    $method = $serviceConfigKey;
                    if ($serviceConfigKey === 'shared') {
                        $method = 'share';
                    }

                    if ($methodCall !== null) {
                        $methodCall = new MethodCall($methodCall, $method);
                        if ($value === false) {
                            $methodCall->args[] = new Arg($this->createFalse());
                        }

                        break;
                    }

                    $this->addLineStmt($this->createMethod($method, $this->toString($value)));

                    break;

                case self::FACTORY:
                case self::CONFIGURATOR:
                    $arrayItems = [];

                    // @todo possibly generic?
                    if (is_array($value)) {
                        foreach ($value as $singleValue) {
                            if ($singleValue instanceof TaggedValue && $singleValue->getTag() === 'service') {
                                $inlineService = $this->createTaggedValueInlineService($value[0]);
                                $arrayItems[] = new Arg($inlineService);
                            } elseif (Strings::startsWith($singleValue, '@')) {
                                $serviceFuncCall = $this->createServiceFuncCall($singleValue);
                                $arrayItems[] = new Arg($serviceFuncCall);
                            } else {
                                $arrayItems[] = new Arg(BuilderHelpers::normalizeValue($singleValue));
                            }
                        }

                        $valueExpr = new Array_($arrayItems);
                    } else {
                        if (Strings::startsWith($value, '@')) {
                            $serviceFuncCall = $this->createServiceFuncCall($value);
                            $valueExpr = new Array_([$serviceFuncCall]);
                        } else {
                            throw new NotImplementedYetException();
                        }
                    }

                $args = [new Arg($valueExpr)];

                if ($methodCall !== null) {
                    $methodCall = new MethodCall($methodCall, 'factory', $args);
                    break;
                }

                    $this->addLineStmt($this->createMethod(
                        $serviceConfigKey,
                        // the handles converting all formats of the single arg
                        $this->toString($value)
                    ));

                    break;

                case self::TAGS:
                    if ($methodCall !== null) {
                        if (count($value) === 1 && is_string($value[0])) {
                            $tagValue = new String_($value[0]);
                            $methodCall = new MethodCall($methodCall, 'tag', [new Arg($tagValue)]);
                            break;
                        }

                        foreach ($value as $singleValue) {
                            $argValues = [];
                            foreach ($singleValue as $singleNestedKey => $singleNestedValue) {
                                if ($singleNestedKey === 'name') {
                                    $argValues[] = BuilderHelpers::normalizeValue($singleNestedValue);
                                    unset($singleValue[$singleNestedKey]);
                                }
                            }

                            $argValues[] = BuilderHelpers::normalizeValue($singleValue);
                            $methodCall = new MethodCall($methodCall, 'tag', $argValues);
                        }

                        break;
                    }

                    foreach ($value as $argValue) {
                        $this->addLineStmt($this->createTagMethod($argValue));
                    }

                    break;

                case self::CALLS:
                    foreach ($value as $singleCall) {
                        $methodName = new String_($singleCall[0] ?? $singleCall['method'] ?? key($singleCall));

                        // @todo can be more items
                        $args = [];
                        $args[] = new Arg($methodName);
                        $args[] = new Arg($this->normalizeValue(
                            $singleCall[1] ?? $singleCall['arguments'] ?? current($singleCall)
                        ));

                        if (isset($singleCall[2]) || isset($singleCall['returns_clone'])) {
                            $returnsCloneValue = $singleCall[2] ?? $singleCall['returns_clone'];
                            $lastMethodCallValue = BuilderHelpers::normalizeValue($returnsCloneValue);
                            $args[] = new Arg($lastMethodCallValue);
                        }

                        $currentArray = current($singleCall);
                        if ($currentArray instanceof TaggedValue) {
                            $args[] = new Arg(BuilderHelpers::normalizeValue(true));
                        }

                        $methodCall = new MethodCall($methodCall, 'call', $args);
                    }

                    break;

                case self::ARGUMENTS:
                    if ($this->isAssociativeArray($value)) {
                        foreach ($value as $argKey => $argValue) {
                            $args = $this->argsNodeFactory->createFromValues([$argKey, $argValue]);
                            $methodCall = new MethodCall($methodCall, 'arg', $args);
                        }
                    } else {
                        foreach ($value as $key => $nestedValue) {
                            if ($nestedValue instanceof TaggedValue) {
                                if ($nestedValue->getTag() === 'service') {
                                    $value = [$this->createTaggedValueInlineService($nestedValue)];
                                    break;
                                }

                                $value[$key] = $this->createTaggedValue($nestedValue);
                            }
                        }

                        $args = $this->argsNodeFactory->createFromValuesAndWrapInArray($value);
                        $methodCall = new MethodCall($methodCall, 'args', $args);
                    }

                    break;

                default:
                    throw new InvalidArgumentException(sprintf(
                        'Unexpected service configuration option: "%s".',
                        $serviceConfigKey
                    ));
            }
        }

        return $methodCall;
    }

    private function addAliasNode($serviceKey, $serviceValues): void
    {
        $servicesVariable = new Variable('services');

        if (class_exists($serviceKey) || interface_exists($serviceKey)) {
            // $this->addUseStatementIfNecessary($serviceValues[self::ALIAS]); - @todo import alias

            $classReference = $this->commonNodeFactory->createClassReference($serviceKey);
            $args = $this->argsNodeFactory->createFromValues([$classReference, $serviceValues[self::ALIAS]]);

            $methodCall = new MethodCall($servicesVariable, 'alias', $args);
            $methodCallExpression = new Expression($methodCall);
            $this->addNode($methodCallExpression);
            $this->addEmptyLine();
            return;
        }

        if ($fullClassName = strstr($serviceKey, ' $', true)) {
            $methodCall = $this->createAliasNode($serviceKey, $fullClassName, $serviceValues);
            $methodCallExpression = new Expression($methodCall);
            $this->addNode($methodCallExpression);
            $this->addEmptyLine();

            return;
        }

        if (isset($serviceValues[self::ALIAS])) {
            $className = $serviceValues[self::ALIAS];

            $classReference = $this->commonNodeFactory->createClassReference($className);
            $args = $this->argsNodeFactory->createFromValues([$serviceKey, $classReference]);
            $methodCall = new MethodCall($servicesVariable, self::ALIAS, $args);
            unset($serviceValues[self::ALIAS]);
        }

        if (is_string($serviceValues) && $serviceValues[0] === '@') {
            $classReference = $this->commonNodeFactory->createClassReference($serviceKey);

            $args = $this->argsNodeFactory->createFromValues([$classReference, $serviceValues], true);
            $methodCall = new MethodCall($servicesVariable, self::ALIAS, $args);
        }

        if (is_array($serviceValues)) {
            $methodCall = $this->convertServiceOptionsToNodes($serviceValues, $methodCall);
        }

        $methodCallExpression = new Expression($methodCall);
        $this->addNode($methodCallExpression);
        $this->addEmptyLine();
    }

    private function createMethod(string $method, ...$argumentStrings): string
    {
        if ($method === 'public' && $argumentStrings[0] === 'false') {
            $method = 'private';
            $argumentStrings[0] = null;
        }

        if ($method === self::ARGUMENTS) {
            $method = 'args';
        }

        return sprintf('->%s(%s)', $method, implode(', ', $argumentStrings));
    }

    private function createDecorateMethod(array $value, MethodCall $methodCall): MethodCall
    {
        $arguments = $this->sortArgumentsByKeyIfExists($value, [
            'decoration_inner_name' => null,
            'decoration_priority' => 0,
            'decoration_on_invalid' => null,
        ]);

        if (isset($arguments['decoration_on_invalid'])) {
            // @todo use normal nodes with FQN
            $arguments['decoration_on_invalid'] = $arguments['decoration_on_invalid'] === 'exception'
                ? $this->commonNodeFactory->createConstFetch(
                    'Symfony\Component\DependencyInjection\ContainerInterface',
                    'EXCEPTION_ON_INVALID_REFERENCE'
                )
                : $this->commonNodeFactory->createConstFetch(
                    'Symfony\Component\DependencyInjection\ContainerInterface',
                    'IGNORE_ON_INVALID_REFERENCE'
                );
        }

        // Don't write the next arguments if they are null.
        if ($arguments['decoration_on_invalid'] === null && $arguments['decoration_priority'] === 0) {
            unset($arguments['decoration_on_invalid'], $arguments['decoration_priority']);

            if ($arguments['decoration_inner_name'] === null) {
                unset($arguments['decoration_inner_name']);
            }
        }

        array_unshift($arguments, $value['decorates']);

        $args = [];
        foreach ($arguments as $argument) {
            // is class const refrence
            $value = BuilderHelpers::normalizeValue($argument);
            $args[] = new Arg($value);
        }

        return new MethodCall($methodCall, 'decorate', $args);
    }

    private function createTagMethod($value): string
    {
        if (! is_array($value)) {
            $value = ['name' => $value];
        }

        $name = $this->toString($value['name']);
        unset($value['name']);

        if (empty($value)) {
            return $this->createMethod('tag', $name);
        }

        return $this->createMethod('tag', $name, $this->createAssociativeArray($value));
    }

    private function createDeprecateMethod($value, MethodCall $methodCall): MethodCall
    {
        // the old, simple format
        if (! is_array($value)) {
            $args = $this->argsNodeFactory->createFromValues([$value]);
        } else {
            $items = [$value['package'] ?? '', $value['version'] ?? '', $value['message'] ?? ''];

            $args = $this->argsNodeFactory->createFromValues($items);
        }

        return new MethodCall($methodCall, 'deprecate', $args);
    }

    /**
     * @param array $inOrderKeys Pass an array of keys to sort if exists
     *                           or an associative array following this logic [$key => $valueIfNotExists]
     *
     * @return array
     */
    private function sortArgumentsByKeyIfExists(array $arrayToSort, array $inOrderKeys)
    {
        $argumentsInOrder = [];

        if ($this->isAssociativeArray($inOrderKeys)) {
            foreach ($inOrderKeys as $key => $valueIfNotExists) {
                $argumentsInOrder[$key] = $arrayToSort[$key] ?? $valueIfNotExists;
            }

            return $argumentsInOrder;
        }

        foreach ($inOrderKeys as $key) {
            if (isset($arrayToSort[$key])) {
                $argumentsInOrder[] = $arrayToSort[$key];
            }
        }

        return $argumentsInOrder;
    }

    private function addNode(Node $node): void
    {
        $this->stmts[] = $node;
    }

    private function addLineStmt(string $line, ?bool $addBlankLine = null): void
    {
        $addBlankLine = $addBlankLine ? self::EOL_CHAR : null;
        $this->addNode(new Name($line . $addBlankLine));
    }

    private function isAlias(string $serviceKey, $serviceValues): bool
    {
        return isset($serviceValues[self::ALIAS])
            || strstr($serviceKey, ' $', true)
            || is_string($serviceValues) && $serviceValues[0] === '@';
    }

    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * @deprecated
     * @todo remove from here and handle in the printer, once in the end
     */
    private function addEmptyLine(): void
    {
        $this->stmts[] = new Nop();
    }

    private function addImportsToNamespace(Namespace_ $namespace): void
    {
        sort($this->useStatements);

        foreach ($this->useStatements as $className) {
            $useBuilder = new UseBuilder($className, Use_::TYPE_NORMAL);
            $namespace->stmts[] = $useBuilder->getNode();
        }
    }

    private function removeLastEmptyLine(): void
    {
        // remove the last carriage return "\n" if exists.
        $lastStmt = $this->stmts[array_key_last($this->stmts)];

        if ($lastStmt instanceof Name) {
            $lastStmt->parts[0] = rtrim($lastStmt->parts[0], self::EOL_CHAR);
        }

        $lastKey = array_key_last($this->stmts);
        if ($this->stmts[$lastKey] instanceof Nop) {
            unset($this->stmts[$lastKey]);
        }
    }

    private function normalizeValue($value)
    {
        if ($value instanceof TaggedValue) {
            return $this->createServiceReferenceFromTaggedValue($value);
        }

        if (is_array($value)) {
            foreach ($value as $key => $singleValue) {
                if (Strings::startsWith($singleValue, '@')) {
                    $singleValue = ltrim($singleValue, '@');
                    $value[$key] = new FuncCall(new Name('service'), [new Arg(new String_($singleValue))]);
                }
            }

            return BuilderHelpers::normalizeValue($value);
        }

        return $value;
    }

    private function createService(array $serviceValues, string $serviceKey): void
    {
        $class = $serviceValues[self::CLASS_KEY];

        $argValues = $this->createArgs($serviceKey, $class);

        $setMethodCall = new MethodCall(new Variable(VariableName::SERVICES), 'set', $argValues);

        unset($serviceValues[self::CLASS_KEY]);

        $setMethodCall = $this->convertServiceOptionsToNodes($serviceValues, $setMethodCall);
        $setMethodCallExpression = new Expression($setMethodCall);
        $this->addNode($setMethodCallExpression);

        $this->addEmptyLine();
    }

    /**
     * @deprecated
     * @todo use ArgNodesFactory
     */
    private function createArgs(string $serviceKey, string $class): array
    {
        $classReference = $this->commonNodeFactory->createClassReference($class);

        return [new Arg(new String_($serviceKey)), new Arg($classReference)];
    }

    private function createFalse(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }

    private function createServiceReferenceFromTaggedValue(TaggedValue $taggedValue): Array_
    {
        // that's the only value
        if ($taggedValue->getTag() !== 'returns_clone') {
            throw new NotImplementedYetException();
        }

        $serviceName = $taggedValue->getValue()[0];

        $serviceName = ltrim($serviceName, '@');
        $serviceFuncCall = new FuncCall(new Name('service'), [new Arg(new String_($serviceName))]);

        return new Array_([$serviceFuncCall]);
    }

    private function createTaggedValueInlineService(TaggedValue $nestedValue): FuncCall
    {
        $className = $nestedValue->getValue()['class'];
        $classReference = $this->commonNodeFactory->createClassReference($className);

        return new FuncCall(new Name(self::INLINE_SERVICE), [new Arg($classReference)]);
    }

    private function createServiceFuncCall(string $value): FuncCall
    {
        $value = ltrim($value, '@');
        $value = new String_($value);

        return new FuncCall(new Name('service'), [new Arg($value)]);
    }

    private function createAliasNode($serviceKey, string $fullClassName, $serviceValues): MethodCall
    {
        $argument = strstr($serviceKey, '$');

        $methodCall = new MethodCall(new Variable(VariableName::SERVICES), self::ALIAS);

        $classConstReference = $this->commonNodeFactory->createClassReference($fullClassName);
        $concat = new Concat($classConstReference, new String_(' ' . $argument));

        $methodCall->args[] = new Arg($concat);
        $serviceName = ltrim($serviceValues, '@');
        $methodCall->args[] = new Arg(new String_($serviceName));
        return $methodCall;
    }
}
