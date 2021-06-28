<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202106280\Symfony\Component\HttpFoundation\Session\Storage;

use ConfigTransformer202106280\Symfony\Component\HttpFoundation\Request;
/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface SessionStorageFactoryInterface
{
    /**
     * Creates a new instance of SessionStorageInterface
     */
    public function createStorage(?\ConfigTransformer202106280\Symfony\Component\HttpFoundation\Request $request) : \ConfigTransformer202106280\Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
}
