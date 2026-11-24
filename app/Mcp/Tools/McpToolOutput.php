<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class McpToolOutput
{
    /**
     * Build strict outputSchema with withoutAdditionalProperties().
     *
     * @return array<string, Type>
     */
    public static function schema(JsonSchema $schema): array
    {
        return [
            'ok' => $schema->boolean()->required(),
            'tool' => $schema->string()->required(),
            'simulated' => $schema->boolean()->required(),
            'data' => $schema->object()->nullable(),
            'raw' => $schema->string()->nullable(),
            'meta' => $schema->object([
                'role' => $schema->string()->nullable(),
                'timestamp' => $schema->string()->required(),
            ])->withoutAdditionalProperties()->required(),
        ];
    }

    public static function success(string $toolName, mixed $result, bool $simulated = false, ?string $role = null): ResponseFactory
    {
        $normalized = json_decode(json_encode($result, JSON_UNESCAPED_UNICODE), true);

        $payload = [
            'ok' => true,
            'tool' => $toolName,
            'simulated' => $simulated,
            'data' => is_array($normalized) && ! array_is_list($normalized) ? $normalized : (is_array($normalized) ? ['items' => $normalized] : null),
            'raw' => is_array($normalized) ? null : (string) $result,
            'meta' => [
                'role' => $role,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        return Response::structured($payload);
    }
}
