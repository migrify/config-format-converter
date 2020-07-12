<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\Converter\KeyYamlToPhpFactory;

use Migrify\ConfigTransformer\FormatSwitcher\Contract\Converter\KeyYamlToPhpFactoryInterface;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\PhpNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\VariableName;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

final class ParameterKeyYamlToPhpFactory implements KeyYamlToPhpFactoryInterface
{
    /**
     * @var string
     */
    private const PARAMETERS = 'parameters';

    /**
     * @var PhpNodeFactory
     */
    private $phpNodeFactory;

    public function __construct(PhpNodeFactory $phpNodeFactory)
    {
        $this->phpNodeFactory = $phpNodeFactory;
    }

    public function getKey(): string
    {
        return self::PARAMETERS;
    }

    /**
     * @param mixed[] $yaml
     * @return Node[]
     */
    public function convertYamlToNodes(array $yaml): array
    {
        if (count($yaml) === 0) {
            return [];
        }

        $nodes = [];
        $nodes[] = $this->createParametersInit(self::PARAMETERS);

        foreach ($yaml as $parameterName => $value) {
            /** @var string $parameterName */
            $methodCall = $this->phpNodeFactory->createParameterSetMethodCall($parameterName, $value);
            $nodes[] = $methodCall;
        }

        return $nodes;
    }

    private function createParametersInit(string $parameterName): Expression
    {
        $servicesVariable = new Variable($parameterName);
        $containerConfiguratorVariable = new Variable(VariableName::CONTAINER_CONFIGURATOR);

        $assign = new Assign($servicesVariable, new MethodCall($containerConfiguratorVariable, $parameterName));

        return new Expression($assign);
    }
}
