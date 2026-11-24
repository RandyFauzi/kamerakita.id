<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\BatchApproveReportsTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive]
class BatchApproveReportsMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return BatchApproveReportsTool::class;
    }
}
