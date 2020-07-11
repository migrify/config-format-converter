<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory;

use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\VariableName;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

final class ServicesPhpNodeFactory
{
    /**
     * @var string
     */
    private const BIND = 'bind';

    /**
     * @var string
     */
    private const EXCLUDE = 'exclude';

    /**
     * @var CommonNodeFactory
     */
    private $commonNodeFactory;

    /**
     * @var ArgsNodeFactory
     */
    private $argsNodeFactory;

    public function __construct(CommonNodeFactory $commonNodeFactory, ArgsNodeFactory $argsNodeFactory)
    {
        $this->commonNodeFactory = $commonNodeFactory;
        $this->argsNodeFactory = $argsNodeFactory;
    }

    public function createServicesInit(): Expression
    {
        $servicesVariable = $this->createServicesVariable();
        $containerConfiguratorVariable = new Variable(VariableName::CONTAINER_CONFIGURATOR);

        $assign = new Assign($servicesVariable, new MethodCall($containerConfiguratorVariable, 'services'));
        return new Expression($assign);
    }

    public function createResource(string $serviceKey, array $serviceValues): Expression
    {
        $servicesLoadMethodCall = $this->createServicesLoadMethodCall($serviceKey, $serviceValues);

        if (! isset($serviceValues[self::EXCLUDE])) {
            return new Expression($servicesLoadMethodCall);
        }

        $exclude = $serviceValues[self::EXCLUDE];
        if (! is_array($exclude)) {
            $exclude = [$exclude];
        }

        $excludeValue = [];
        foreach ($exclude as $key => $singleExclude) {
            $excludeValue[$key] = $this->commonNodeFactory->createAbsoluteDirExpr($singleExclude);
        }

        $args = $this->argsNodeFactory->createFromValues([$excludeValue]);
        $excludeMethodCall = new MethodCall($servicesLoadMethodCall, self::EXCLUDE, $args);

        return new Expression($excludeMethodCall);
    }

    public function createServiceDefaults(array $serviceValues): Expression
    {
        $methodCall = new MethodCall($this->createServicesVariable(), 'defaults');

        foreach ($serviceValues as $key => $value) {
            if (in_array($key, ['autowire', 'autoconfigure', 'public'], true)) {
                $methodCall = new MethodCall($methodCall, $key);
                if ($value === false) {
                    $methodCall->args[] = new Arg($this->createFalse());
                }
            }

            if ($key === self::BIND) {
                $methodCall = $this->createBindMethodCall($methodCall, $serviceValues[self::BIND]);
            }
        }

        return new Expression($methodCall);
    }

    private function createServicesLoadMethodCall(string $serviceKey, $serviceValues): MethodCall
    {
        $servicesVariable = $this->createServicesVariable();

        $resource = $serviceValues['resource'];

        $args = [];
        $args[] = new Arg(new String_($serviceKey));
        $args[] = new Arg($this->commonNodeFactory->createAbsoluteDirExpr($resource));

        return new MethodCall($servicesVariable, 'load', $args);
    }

    private function createBindMethodCall(MethodCall $methodCall, array $bindValues): MethodCall
    {
        foreach ($bindValues as $key => $value) {
            $args = $this->argsNodeFactory->createFromValues([$key, $value]);
            $methodCall = new MethodCall($methodCall, self::BIND, $args);
        }

        return $methodCall;
    }

    private function createServicesVariable(): Variable
    {
        return new Variable(VariableName::SERVICES);
    }

    private function createFalse(): ConstFetch
    {
        return new ConstFetch(new Name('false'));
    }
}
