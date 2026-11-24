<?php

namespace App\Mcp\Tools\Adapter;

use App\Mcp\Contracts\McpToolInterface;
use App\Mcp\Tools\McpToolOutput;
use App\Services\McpAccess;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\JsonSchema as JsonSchemaFactory;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

abstract class LegacyToolAdapter extends Tool
{
    private ?McpToolInterface $resolvedTool = null;

    /**
     * @return class-string<McpToolInterface>
     */
    abstract protected function legacyToolClass(): string;

    protected function legacyTool(): McpToolInterface
    {
        if ($this->resolvedTool === null) {
            $class = $this->legacyToolClass();
            $this->resolvedTool = app($class);
        }

        return $this->resolvedTool;
    }

    public function name(): string
    {
        return $this->legacyTool()->getName();
    }

    public function title(): string
    {
        return $this->title !== ''
            ? $this->title
            : ucwords(str_replace('_', ' ', $this->name()));
    }

    public function description(): string
    {
        return $this->legacyTool()->getDescription();
    }

    public function shouldRegister(): bool
    {
        return app(McpAccess::class)->allows($this->legacyTool()->getRequiredPermission());
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return McpToolOutput::schema($schema);
    }

    public function toArray(): array
    {
        $legacyParams = $this->legacyTool()->getParameters();

        if (! isset($legacyParams['type'])) {
            $legacyParams['type'] = 'object';
        }

        if (! isset($legacyParams['properties']) || empty($legacyParams['properties'])) {
            $legacyParams['properties'] = (object) [];
        }

        $outputSchema = JsonSchemaFactory::object(
            $this->outputSchema(...),
        )->toArray();

        $result = [
            'name' => $this->name(),
            'title' => $this->title(),
            'description' => $this->description(),
            'inputSchema' => $legacyParams,
            'annotations' => [
                'title' => $this->title(),
                ...$this->annotations(),
            ],
        ];

        if (isset($outputSchema['properties'])) {
            $outputSchema['additionalProperties'] = false;
            $result['outputSchema'] = $outputSchema;
        }

        return $this->mergeMeta($this->mergeIcons($result));
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $access = app(McpAccess::class);
        $requiredPermission = $this->legacyTool()->getRequiredPermission();

        if (! $access->allows($requiredPermission)) {
            return Response::error("Permission denied. Missing '{$requiredPermission}' ability.");
        }

        $args = $request->all();
        $isSimulation = (bool) ($args['is_simulation'] ?? false);
        $clientContext = $access->clientContext(request());

        try {
            if ($isSimulation) {
                DB::beginTransaction();
            }

            $result = $this->legacyTool()->execute($args, $clientContext);

            if ($isSimulation) {
                DB::rollBack();
                $result = [
                    'simulation_status' => 'success',
                    'message' => 'Dry-run successful. No data was actually modified.',
                    'preview' => $result,
                ];
            }

            return McpToolOutput::success(
                toolName: $this->name(),
                result: $result,
                simulated: $isSimulation,
                role: $clientContext['role'] ?? null,
            );
        } catch (Throwable $e) {
            if ($isSimulation && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return Response::error($e->getMessage());
        }
    }
}
