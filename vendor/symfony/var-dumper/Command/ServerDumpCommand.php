<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ConfigTransformer202107154\Symfony\Component\VarDumper\Command;

use ConfigTransformer202107154\Symfony\Component\Console\Command\Command;
use ConfigTransformer202107154\Symfony\Component\Console\Exception\InvalidArgumentException;
use ConfigTransformer202107154\Symfony\Component\Console\Input\InputInterface;
use ConfigTransformer202107154\Symfony\Component\Console\Input\InputOption;
use ConfigTransformer202107154\Symfony\Component\Console\Output\OutputInterface;
use ConfigTransformer202107154\Symfony\Component\Console\Style\SymfonyStyle;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Cloner\Data;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Dumper\CliDumper;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Dumper\HtmlDumper;
use ConfigTransformer202107154\Symfony\Component\VarDumper\Server\DumpServer;
/**
 * Starts a dump server to collect and output dumps on a single place with multiple formats support.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 *
 * @final
 */
class ServerDumpCommand extends \ConfigTransformer202107154\Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'server:dump';
    protected static $defaultDescription = 'Start a dump server that collects and displays dumps in a single place';
    private $server;
    /** @var DumpDescriptorInterface[] */
    private $descriptors;
    public function __construct(\ConfigTransformer202107154\Symfony\Component\VarDumper\Server\DumpServer $server, array $descriptors = [])
    {
        $this->server = $server;
        $this->descriptors = $descriptors + ['cli' => new \ConfigTransformer202107154\Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor(new \ConfigTransformer202107154\Symfony\Component\VarDumper\Dumper\CliDumper()), 'html' => new \ConfigTransformer202107154\Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor(new \ConfigTransformer202107154\Symfony\Component\VarDumper\Dumper\HtmlDumper())];
        parent::__construct();
    }
    protected function configure()
    {
        $availableFormats = \implode(', ', \array_keys($this->descriptors));
        $this->addOption('format', null, \ConfigTransformer202107154\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, \sprintf('The output format (%s)', $availableFormats), 'cli')->setDescription(self::$defaultDescription)->setHelp(<<<'EOF'
<info>%command.name%</info> starts a dump server that collects and displays
dumps in a single place for debugging you application:

  <info>php %command.full_name%</info>

You can consult dumped data in HTML format in your browser by providing the <comment>--format=html</comment> option
and redirecting the output to a file:

  <info>php %command.full_name% --format="html" > dump.html</info>

EOF
);
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output) : int
    {
        $io = new \ConfigTransformer202107154\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!($descriptor = $this->descriptors[$format] ?? null)) {
            throw new \ConfigTransformer202107154\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('Unsupported format "%s".', $format));
        }
        $errorIo = $io->getErrorStyle();
        $errorIo->title('Symfony Var Dumper Server');
        $this->server->start();
        $errorIo->success(\sprintf('Server listening on %s', $this->server->getHost()));
        $errorIo->comment('Quit the server with CONTROL-C.');
        $this->server->listen(function (\ConfigTransformer202107154\Symfony\Component\VarDumper\Cloner\Data $data, array $context, int $clientId) use($descriptor, $io) {
            $descriptor->describe($io, $data, $context, $clientId);
        });
        return 0;
    }
}
