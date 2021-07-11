<?php



/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use ConfigTransformer202107116\Symfony\Polyfill\Php81 as p;
if (\PHP_VERSION_ID >= 80100) {
    return;
}
if (\defined('MYSQLI_REFRESH_SLAVE') && !\defined('MYSQLI_REFRESH_REPLICA')) {
    \define('MYSQLI_REFRESH_REPLICA', 64);
}
if (!\function_exists('ConfigTransformer202107116\\array_is_list')) {
    function array_is_list(array $array) : bool
    {
        return \ConfigTransformer202107116\Symfony\Polyfill\Php81\Php81::array_is_list($array);
    }
}
if (!\function_exists('ConfigTransformer202107116\\enum_exists')) {
    function enum_exists(string $enum, bool $autoload = \true) : bool
    {
        return $autoload && \class_exists($enum) && \false;
    }
}
