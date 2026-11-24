<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\MatchPayrollExcelTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class MatchPayrollExcelMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return MatchPayrollExcelTool::class;
    }
}
