<?php

namespace ConfigTransformer202107101\Psr\Log;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;
    /**
     * Sets a logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}