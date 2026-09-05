<?php

namespace App\Mcp;

use App\Mcp\Contracts\McpToolInterface;
use Exception;

class McpToolRegistry
{
    /** @var array<string, McpToolInterface> */
    protected array $tools = [];

    /**
     * Register a new tool.
     */
    public function register(McpToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Get a specific tool by name.
     */
    public function getTool(string $name): ?McpToolInterface
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Get all registered tools, formatted for the MCP 'tools/list' response.
     */
    public function getRegisteredToolsFormatted(array $client = null): array
    {
        $formatted = [];
        $clientPermissions = $client['permissions'] ?? [];

        foreach ($this->tools as $tool) {
            // Only expose tools that the client has permission to use
            if ($this->hasPermission($clientPermissions, $tool->getRequiredPermission())) {
                $formatted[] = [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => $tool->getParameters(),
                ];
            }
        }

        return $formatted;
    }

    /**
     * Execute a tool.
     */
    public function execute(string $name, array $args, array $client)
    {
        $tool = $this->getTool($name);

        if (!$tool) {
            throw new Exception("Unknown tool: {$name}");
        }

        if (!$this->hasPermission($client['permissions'] ?? [], $tool->getRequiredPermission())) {
            throw new Exception("Permission denied. Client lacks '{$tool->getRequiredPermission()}' permission.");
        }

        return $tool->execute($args, $client);
    }

    /**
     * Check if client has required permission.
     */
    protected function hasPermission(array $clientPermissions, string $requiredPermission): bool
    {
        if (in_array('*', $clientPermissions)) {
            return true;
        }

        return in_array($requiredPermission, $clientPermissions);
    }
}
