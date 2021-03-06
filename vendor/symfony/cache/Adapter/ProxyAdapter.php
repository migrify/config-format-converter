<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202107154\Symfony\Component\Cache\Adapter;

use ConfigTransformer202107154\Psr\Cache\CacheItemInterface;
use ConfigTransformer202107154\Psr\Cache\CacheItemPoolInterface;
use ConfigTransformer202107154\Symfony\Component\Cache\CacheItem;
use ConfigTransformer202107154\Symfony\Component\Cache\PruneableInterface;
use ConfigTransformer202107154\Symfony\Component\Cache\ResettableInterface;
use ConfigTransformer202107154\Symfony\Component\Cache\Traits\ContractsTrait;
use ConfigTransformer202107154\Symfony\Component\Cache\Traits\ProxyTrait;
use ConfigTransformer202107154\Symfony\Contracts\Cache\CacheInterface;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ProxyAdapter implements \ConfigTransformer202107154\Symfony\Component\Cache\Adapter\AdapterInterface, \ConfigTransformer202107154\Symfony\Contracts\Cache\CacheInterface, \ConfigTransformer202107154\Symfony\Component\Cache\PruneableInterface, \ConfigTransformer202107154\Symfony\Component\Cache\ResettableInterface
{
    use ContractsTrait;
    use ProxyTrait;
    private $namespace = '';
    private $namespaceLen;
    private $poolHash;
    private $defaultLifetime;
    private static $createCacheItem;
    private static $setInnerItem;
    public function __construct(\ConfigTransformer202107154\Psr\Cache\CacheItemPoolInterface $pool, string $namespace = '', int $defaultLifetime = 0)
    {
        $this->pool = $pool;
        $this->poolHash = $poolHash = \spl_object_hash($pool);
        if ('' !== $namespace) {
            \assert('' !== \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::validateKey($namespace));
            $this->namespace = $namespace;
        }
        $this->namespaceLen = \strlen($namespace);
        $this->defaultLifetime = $defaultLifetime;
        self::$createCacheItem ?? (self::$createCacheItem = \Closure::bind(static function ($key, $innerItem, $poolHash) {
            $item = new \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem();
            $item->key = $key;
            if (null === $innerItem) {
                return $item;
            }
            $item->value = $v = $innerItem->get();
            $item->isHit = $innerItem->isHit();
            $item->innerItem = $innerItem;
            $item->poolHash = $poolHash;
            // Detect wrapped values that encode for their expiry and creation duration
            // For compactness, these values are packed in the key of an array using
            // magic numbers in the form 9D-..-..-..-..-00-..-..-..-5F
            if (\is_array($v) && 1 === \count($v) && 10 === \strlen($k = (string) \key($v)) && "�" === $k[0] && "\0" === $k[5] && "_" === $k[9]) {
                $item->value = $v[$k];
                $v = \unpack('Ve/Nc', \substr($k, 1, -1));
                $item->metadata[\ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::METADATA_EXPIRY] = $v['e'] + \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::METADATA_EXPIRY_OFFSET;
                $item->metadata[\ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::METADATA_CTIME] = $v['c'];
            } elseif ($innerItem instanceof \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem) {
                $item->metadata = $innerItem->metadata;
            }
            $innerItem->set(null);
            return $item;
        }, null, \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::class));
        self::$setInnerItem ?? (self::$setInnerItem = \Closure::bind(
            /**
             * @param array $item A CacheItem cast to (array); accessing protected properties requires adding the "\0*\0" PHP prefix
             */
            static function (\ConfigTransformer202107154\Psr\Cache\CacheItemInterface $innerItem, array $item) {
                // Tags are stored separately, no need to account for them when considering this item's newly set metadata
                if (isset(($metadata = $item["\0*\0newMetadata"])[\ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::METADATA_TAGS])) {
                    unset($metadata[\ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::METADATA_TAGS]);
                }
                if ($metadata) {
                    // For compactness, expiry and creation duration are packed in the key of an array, using magic numbers as separators
                    $item["\0*\0value"] = ["�" . \pack('VN', (int) (0.1 + $metadata[self::METADATA_EXPIRY] - self::METADATA_EXPIRY_OFFSET), $metadata[self::METADATA_CTIME]) . "_" => $item["\0*\0value"]];
                }
                $innerItem->set($item["\0*\0value"]);
                $innerItem->expiresAt(null !== $item["\0*\0expiry"] ? \DateTime::createFromFormat('U.u', \sprintf('%.6F', 0 === $item["\0*\0expiry"] ? \PHP_INT_MAX : $item["\0*\0expiry"])) : null);
            },
            null,
            \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::class
        ));
    }
    /**
     * {@inheritdoc}
     * @param string $key
     * @param callable $callback
     * @param float|null $beta
     * @param mixed[]|null $metadata
     */
    public function get($key, $callback, $beta = null, &$metadata = null)
    {
        if (!$this->pool instanceof \ConfigTransformer202107154\Symfony\Contracts\Cache\CacheInterface) {
            return $this->doGet($this, $key, $callback, $beta, $metadata);
        }
        return $this->pool->get($this->getId($key), function ($innerItem, bool &$save) use($key, $callback) {
            $item = (self::$createCacheItem)($key, $innerItem, $this->poolHash);
            $item->set($value = $callback($item, $save));
            (self::$setInnerItem)($innerItem, (array) $item);
            return $value;
        }, $beta, $metadata);
    }
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $item = $this->pool->getItem($this->getId($key));
        return (self::$createCacheItem)($key, $item, $this->poolHash);
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $keys
     */
    public function getItems($keys = [])
    {
        if ($this->namespaceLen) {
            foreach ($keys as $i => $key) {
                $keys[$i] = $this->getId($key);
            }
        }
        return $this->generateItems($this->pool->getItems($keys));
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function hasItem($key)
    {
        return $this->pool->hasItem($this->getId($key));
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param string $prefix
     */
    public function clear($prefix = '')
    {
        if ($this->pool instanceof \ConfigTransformer202107154\Symfony\Component\Cache\Adapter\AdapterInterface) {
            return $this->pool->clear($this->namespace . $prefix);
        }
        return $this->pool->clear();
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function deleteItem($key)
    {
        return $this->pool->deleteItem($this->getId($key));
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param mixed[] $keys
     */
    public function deleteItems($keys)
    {
        if ($this->namespaceLen) {
            foreach ($keys as $i => $key) {
                $keys[$i] = $this->getId($key);
            }
        }
        return $this->pool->deleteItems($keys);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function save($item)
    {
        return $this->doSave($item, __FUNCTION__);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function saveDeferred($item)
    {
        return $this->doSave($item, __FUNCTION__);
    }
    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pool->commit();
    }
    private function doSave(\ConfigTransformer202107154\Psr\Cache\CacheItemInterface $item, string $method)
    {
        if (!$item instanceof \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem) {
            return \false;
        }
        $item = (array) $item;
        if (null === $item["\0*\0expiry"] && 0 < $this->defaultLifetime) {
            $item["\0*\0expiry"] = \microtime(\true) + $this->defaultLifetime;
        }
        if ($item["\0*\0poolHash"] === $this->poolHash && $item["\0*\0innerItem"]) {
            $innerItem = $item["\0*\0innerItem"];
        } elseif ($this->pool instanceof \ConfigTransformer202107154\Symfony\Component\Cache\Adapter\AdapterInterface) {
            // this is an optimization specific for AdapterInterface implementations
            // so we can save a round-trip to the backend by just creating a new item
            $innerItem = (self::$createCacheItem)($this->namespace . $item["\0*\0key"], null, $this->poolHash);
        } else {
            $innerItem = $this->pool->getItem($this->namespace . $item["\0*\0key"]);
        }
        (self::$setInnerItem)($innerItem, $item);
        return $this->pool->{$method}($innerItem);
    }
    private function generateItems(iterable $items)
    {
        $f = self::$createCacheItem;
        foreach ($items as $key => $item) {
            if ($this->namespaceLen) {
                $key = \substr($key, $this->namespaceLen);
            }
            (yield $key => $f($key, $item, $this->poolHash));
        }
    }
    private function getId($key) : string
    {
        \assert('' !== \ConfigTransformer202107154\Symfony\Component\Cache\CacheItem::validateKey($key));
        return $this->namespace . $key;
    }
}
