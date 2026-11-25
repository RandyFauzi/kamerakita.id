<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\ListFastworkOnboardingsTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListFastworkOnboardingsMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return ListFastworkOnboardingsTool::class;
    }
}