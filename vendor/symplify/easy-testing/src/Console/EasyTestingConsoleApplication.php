<?php

declare (strict_types=1);
namespace ConfigTransformer2021061210\Symplify\EasyTesting\Console;

use ConfigTransformer2021061210\Symfony\Component\Console\Application;
use ConfigTransformer2021061210\Symfony\Component\Console\Command\Command;
use ConfigTransformer2021061210\Symplify\PackageBuilder\Console\Command\CommandNaming;
final class EasyTestingConsoleApplication extends \ConfigTransformer2021061210\Symfony\Component\Console\Application
{
    /**
     * @param Command[] $commands
     */
    public function __construct(\ConfigTransformer2021061210\Symplify\PackageBuilder\Console\Command\CommandNaming $commandNaming, array $commands)
    {
        foreach ($commands as $command) {
            $commandName = $commandNaming->resolveFromCommand($command);
            $command->setName($commandName);
            $this->add($command);
        }
        parent::__construct('Easy Testing');
    }
}
