<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\Tests\Converter\ConfigFormatConverter\XmlToYaml;

use Iterator;
use Migrify\ConfigTransformer\FormatSwitcher\Configuration\Configuration;
use Migrify\ConfigTransformer\FormatSwitcher\Tests\Converter\ConfigFormatConverter\AbstractConfigFormatConverterTest;
use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\SymfonyVersionFeature;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class XmlToYamlTest extends AbstractConfigFormatConverterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Configuration $configuration */
        $configuration = self::$container->get(Configuration::class);
        $configuration->changeSymfonyVersion(SymfonyVersionFeature::SERVICE_WITHOUT_NAME - 0.1);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $this->doTestOutput($fixtureFileInfo, 'xml', 'yaml');
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.xml');
    }
}
