<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\MailboxSummaryTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class MailboxSummaryMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return MailboxSummaryTool::class;
    }
}