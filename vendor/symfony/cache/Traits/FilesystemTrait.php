<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202107116\Symfony\Component\Cache\Traits;

use ConfigTransformer202107116\Symfony\Component\Cache\Exception\CacheException;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Rob Frawley 2nd <rmf@src.run>
 *
 * @internal
 */
trait FilesystemTrait
{
    use FilesystemCommonTrait;
    private $marshaller;
    /**
     * @return bool
     */
    public function prune()
    {
        $time = \time();
        $pruned = \true;
        foreach ($this->scanHashDir($this->directory) as $file) {
            if (!($h = @\fopen($file, 'r'))) {
                continue;
            }
            if (($expiresAt = (int) \fgets($h)) && $time >= $expiresAt) {
                \fclose($h);
                $pruned = @\unlink($file) && !\file_exists($file) && $pruned;
            } else {
                \fclose($h);
            }
        }
        return $pruned;
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $ids
     */
    protected function doFetch($ids)
    {
        $values = [];
        $now = \time();
        foreach ($ids as $id) {
            $file = $this->getFile($id);
            if (!\is_file($file) || !($h = @\fopen($file, 'r'))) {
                continue;
            }
            if (($expiresAt = (int) \fgets($h)) && $now >= $expiresAt) {
                \fclose($h);
                @\unlink($file);
            } else {
                $i = \rawurldecode(\rtrim(\fgets($h)));
                $value = \stream_get_contents($h);
                \fclose($h);
                if ($i === $id) {
                    $values[$id] = $this->marshaller->unmarshall($value);
                }
            }
        }
        return $values;
    }
    /**
     * {@inheritdoc}
     * @param string $id
     */
    protected function doHave($id)
    {
        $file = $this->getFile($id);
        return \is_file($file) && (@\filemtime($file) > \time() || $this->doFetch([$id]));
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $values
     * @param int $lifetime
     */
    protected function doSave($values, $lifetime)
    {
        $expiresAt = $lifetime ? \time() + $lifetime : 0;
        $values = $this->marshaller->marshall($values, $failed);
        foreach ($values as $id => $value) {
            if (!$this->write($this->getFile($id, \true), $expiresAt . "\n" . \rawurlencode($id) . "\n" . $value, $expiresAt)) {
                $failed[] = $id;
            }
        }
        if ($failed && !\is_writable($this->directory)) {
            throw new \ConfigTransformer202107116\Symfony\Component\Cache\Exception\CacheException(\sprintf('Cache directory is not writable (%s).', $this->directory));
        }
        return $failed;
    }
    private function getFileKey(string $file) : string
    {
        if (!($h = @\fopen($file, 'r'))) {
            return '';
        }
        \fgets($h);
        // expiry
        $encodedKey = \fgets($h);
        \fclose($h);
        return \rawurldecode(\rtrim($encodedKey));
    }
}
