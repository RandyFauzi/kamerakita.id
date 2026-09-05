<?php

namespace App\Mcp\Contracts;

interface McpToolInterface
{
    /**
     * Get the tool name.
     */
    public function getName(): string;

    /**
     * Get the tool description.
     */
    public function getDescription(): string;

    /**
     * Get the JSON schema for tool parameters.
     */
    public function getParameters(): array;

    /**
     * Get the required permission to execute this tool.
     */
    public function getRequiredPermission(): string;

    /**
     * Execute the tool logic.
     * 
     * @param array $args
     * @param array $client
     * @return mixed
     */
    public function execute(array $args, array $client);
}
