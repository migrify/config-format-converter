<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FeatureShifter\Yaml;

use Migrify\ConfigTransformer\FeatureShifter\ValueObject\ServiceConfig;
use Nette\Utils\Strings;

final class CommonNamespaceResolver
{
    /**
     * @return string[]
     */
    public function resolve(ServiceConfig $serviceConfig, int $nestingLevel): array
    {
        if ($serviceConfig->getClasses() === []) {
            return [];
        }

        $namespaces = [];
        foreach ($serviceConfig->getClasses() as $class) {
            $namespace = Strings::before($class, '\\', $nestingLevel);
            if ($namespace) {
                $namespaces[] = $namespace;
            }
        }

        if (count($namespaces) > 0) {
            return array_unique($namespaces);
        }

        // reiterate with less strict nesting
        return $this->resolve($serviceConfig, --$nestingLevel);
    }
}
