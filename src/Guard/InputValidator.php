<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\Guard;

use Migrify\ConfigTransformer\Exception\InvalidConfigurationException;

final class InputValidator
{
    /**
     * @param string[] $allowedValues
     */
    public function validateFormatValue(
        string $formatValue,
        array $allowedValues,
        string $optionKey,
        string $type
    ): void {
        if ($formatValue === '') {
            $message = sprintf('Add missing "--%s" option to command line', $optionKey);
            throw new InvalidConfigurationException($message);
        }

        if (in_array($formatValue, $allowedValues, true)) {
            return;
        }

        $message = sprintf(
            '%s format "%s" is not supported. Pick one of "%s"',
            ucfirst($type),
            $formatValue,
            implode('", ', $allowedValues)
        );

        throw new InvalidConfigurationException($message);
    }
}
