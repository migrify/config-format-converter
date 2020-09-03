<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\RoutingCaseConverter;

use Migrify\ConfigTransformer\FormatSwitcher\Contract\RoutingCaseConverterInterface;
use Migrify\PhpConfigPrinter\NodeFactory\ArgsNodeFactory;
use Migrify\PhpConfigPrinter\ValueObject\VariableName;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Symplify\PackageBuilder\Strings\StringFormatConverter;
use function explode;
use function is_string;

final class ImportRoutingCaseConverter implements RoutingCaseConverterInterface
{
    /**
     * @var string[]
     */
    private const NESTED_KEYS = [
        'name_prefix',
        'defaults',
        'requirements',
        'options',
        'utf8',
        'condition',
        'host',
        'schemes',
        self::METHODS,
        'controller',
        'locale',
        'format',
        'stateless',
    ];

    /**
     * @var string[]
     */
    private const IMPORT_ARGS = [self::RESOURCE, self::TYPE, self::EXCLUDE];

    /**
     * @var string[]
     */
    private const PREFIX_ARGS = [
        // Add prefix itself as first argument
        self::PREFIX,
        'trailing_slash_on_root',
    ];

    /**
     * @var string
     */
    private const PREFIX = 'prefix';

    /**
     * @var string
     */
    private const RESOURCE = 'resource';

    /**
     * @var string
     */
    private const TYPE = 'type';

    /**
     * @var string
     */
    private const EXCLUDE = 'exclude';

    /**
     * @var string
     */
    private const METHODS = 'methods';

    /**
     * @var ArgsNodeFactory
     */
    private $argsNodeFactory;

    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    public function __construct(ArgsNodeFactory $argsNodeFactory)
    {
        $this->argsNodeFactory = $argsNodeFactory;
        $this->stringFormatConverter = new StringFormatConverter();
    }

    public function match(string $key, $values): bool
    {
        return isset($values[self::RESOURCE]);
    }

    public function convertToMethodCall(string $key, $values): Expression
    {
        $variable = new Variable(VariableName::ROUTING_CONFIGURATOR);

        $args = $this->createAddArgs(self::IMPORT_ARGS, $values);
        $methodCall = new MethodCall($variable, 'import', $args);

        // Handle prefix independently as it has specific args
        if (isset($values[self::PREFIX])) {
            $args = $this->createAddArgs(self::PREFIX_ARGS, $values);
            $methodCall = new MethodCall($methodCall, self::PREFIX, $args);
        }

        foreach (self::NESTED_KEYS as $nestedKey) {
            if (! isset($values[$nestedKey])) {
                continue;
            }

            $nestedValues = $values[$nestedKey];

            // Transform methods as string GET|HEAD to array
            if ($nestedKey === self::METHODS && is_string($nestedValues)) {
                $nestedValues = explode('|', $nestedValues);
            }

            $args = $this->argsNodeFactory->createFromValues([$nestedValues]);
            $name = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($nestedKey);

            $methodCall = new MethodCall($methodCall, $name, $args);
        }

        return new Expression($methodCall);
    }

    /**
     * @param string[] $argsNames
     * @param mixed $values
     * @return Arg[]
     */
    private function createAddArgs(array $argsNames, $values): array
    {
        $argumentValues = [];

        foreach ($argsNames as $arg) {
            if (isset($values[$arg])) {
                // Default $ignoreErrors to false before $exclude on import(), same behaviour as symfony
                if ($arg === self::EXCLUDE) {
                    $argumentValues[] = false;
                }

                $argumentValues[] = $values[$arg];
            }
        }

        return $this->argsNodeFactory->createFromValues($argumentValues);
    }
}
