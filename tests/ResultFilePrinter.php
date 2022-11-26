<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\DefaultResultPrinter;

class ResultFilePrinter extends DefaultResultPrinter
{
    public function printResult(TestResult $result): void
    {
        parent::printResult($result);

        ob_start();
        parent::printFooter($result);
        file_put_contents("phpunit.out", ob_get_contents());
        ob_end_clean();
    }
}
