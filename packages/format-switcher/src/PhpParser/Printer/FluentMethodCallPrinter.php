<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;

final class FluentMethodCallPrinter extends Standard
{
    /**
     * @var string
     */
    private const EOL_CHAR = "\n";

    public function prettyPrintFile(array $stmts): string
    {
        $printedContent = parent::prettyPrintFile($stmts);

        // remove trailing spaces
        $printedContent = Strings::replace($printedContent, '#^[ ]+\n#m', "\n");

        // remove space before " :" in main closure
        $printedContent = Strings::replace(
            $printedContent,
            '#containerConfigurator\) : void#',
            'containerConfigurator): void'
        );

        return $printedContent . self::EOL_CHAR;
    }

    protected function pExpr_Array(Array_ $array): string
    {
        $array->setAttribute('kind', Array_::KIND_SHORT);

        return parent::pExpr_Array($array);
    }

    protected function pExpr_MethodCall(MethodCall $methodCall): string
    {
        $printedMethodCall = parent::pExpr_MethodCall($methodCall);
        return $this->indentFluentCallToNewline($printedMethodCall);
    }

    private function indentFluentCallToNewline(string $content): string
    {
        $nextCallIndentReplacement = ')' . PHP_EOL . Strings::indent('->', 8, ' ');
        return Strings::replace($content, '#\)->#', $nextCallIndentReplacement);
    }
}
