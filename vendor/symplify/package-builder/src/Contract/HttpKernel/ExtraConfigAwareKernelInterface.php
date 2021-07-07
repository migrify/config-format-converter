<?php

declare (strict_types=1);
namespace ConfigTransformer202107076\Symplify\PackageBuilder\Contract\HttpKernel;

use ConfigTransformer202107076\Symfony\Component\HttpKernel\KernelInterface;
use ConfigTransformer202107076\Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends \ConfigTransformer202107076\Symfony\Component\HttpKernel\KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs(array $configs) : void;
}
