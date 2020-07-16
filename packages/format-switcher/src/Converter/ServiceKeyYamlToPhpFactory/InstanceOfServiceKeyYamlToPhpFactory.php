<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\Converter\ServiceKeyYamlToPhpFactory;

use Migrify\ConfigTransformer\FeatureShifter\ValueObject\YamlKey;
use Migrify\ConfigTransformer\FormatSwitcher\Contract\Converter\ServiceKeyYamlToPhpFactoryInterface;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\CommonNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory\Service\ServiceOptionNodeFactory;
use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\VariableName;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

/**
 * Handles this part:
 *
 * services:
 *     _instanceof: <---
 */
final class InstanceOfServiceKeyYamlToPhpFactory implements ServiceKeyYamlToPhpFactoryInterface
{
    /**
     * @var CommonNodeFactory
     */
    private $commonNodeFactory;

    /**
     * @var ServiceOptionNodeFactory
     */
    private $serviceOptionNodeFactory;

    public function __construct(
        CommonNodeFactory $commonNodeFactory,
        ServiceOptionNodeFactory $serviceOptionNodeFactory
    ) {
        $this->commonNodeFactory = $commonNodeFactory;
        $this->serviceOptionNodeFactory = $serviceOptionNodeFactory;
    }

    public function convertYamlToNodes($key, $yaml): array
    {
        $nodes = [];

        foreach ($yaml as $instanceKey => $instanceValues) {
            $classReference = $this->commonNodeFactory->createClassReference($instanceKey);

            $servicesVariable = new Variable(VariableName::SERVICES);
            $args = [new Arg($classReference)];

            $instanceofMethodCall = new MethodCall($servicesVariable, 'instanceof', $args);
            $instanceofMethodCall = $this->serviceOptionNodeFactory->convertServiceOptionsToNodes(
                $instanceValues,
                $instanceofMethodCall
            );

            $nodes[] = new Expression($instanceofMethodCall);
        }

        return $nodes;
    }

    public function isMatch($key, $values): bool
    {
        return $key === YamlKey::_INSTANCEOF;
    }
}
