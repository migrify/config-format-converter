<?php

declare (strict_types=1);
namespace ConfigTransformer202107154\Symplify\ConfigTransformer\Configuration;

use ConfigTransformer202107154\Symfony\Component\Console\Input\InputInterface;
use ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Format;
use ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Option;
use ConfigTransformer202107154\Symplify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface;
final class Configuration implements \ConfigTransformer202107154\Symplify\PhpConfigPrinter\Contract\SymfonyVersionFeatureGuardInterface
{
    /**
     * @var string[]
     */
    private $source = [];
    /**
     * @var float|null
     */
    private $targetSymfonyVersion;
    /**
     * @var bool
     */
    private $isDryRun = \false;
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    public function populateFromInput($input) : void
    {
        $this->source = (array) $input->getArgument(\ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Option::SOURCES);
        $this->targetSymfonyVersion = \floatval($input->getOption(\ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Option::TARGET_SYMFONY_VERSION));
        $this->isDryRun = \boolval($input->getOption(\ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Option::DRY_RUN));
    }
    /**
     * @return string[]
     */
    public function getSource() : array
    {
        return $this->source;
    }
    /**
     * @param float $symfonyVersion
     */
    public function isAtLeastSymfonyVersion($symfonyVersion) : bool
    {
        return $this->targetSymfonyVersion >= $symfonyVersion;
    }
    public function isDryRun() : bool
    {
        return $this->isDryRun;
    }
    /**
     * @param float $symfonyVersion
     */
    public function changeSymfonyVersion($symfonyVersion) : void
    {
        $this->targetSymfonyVersion = $symfonyVersion;
    }
    /**
     * @return string[]
     */
    public function getInputSuffixes() : array
    {
        return [\ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Format::YAML, \ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Format::YML, \ConfigTransformer202107154\Symplify\ConfigTransformer\ValueObject\Format::XML];
    }
}
