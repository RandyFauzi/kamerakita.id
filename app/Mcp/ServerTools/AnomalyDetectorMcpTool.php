<?php

namespace App\Mcp\ServerTools;

use App\Mcp\Tools\AnomalyDetectorTool;
use App\Mcp\Tools\Adapter\LegacyToolAdapter;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class AnomalyDetectorMcpTool extends LegacyToolAdapter
{
    protected function legacyToolClass(): string
    {
        return AnomalyDetectorTool::class;
    }
}
