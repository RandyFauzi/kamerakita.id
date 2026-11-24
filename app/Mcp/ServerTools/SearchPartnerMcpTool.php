<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\SearchPartnerTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SearchPartnerMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return SearchPartnerTool::class;
    }
}
