<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\TopPartnersTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class TopPartnersMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return TopPartnersTool::class;
    }
}
