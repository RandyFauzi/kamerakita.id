<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mcp\McpToolRegistry;
use App\Mcp\Tools\SearchPartnerTool;
use App\Mcp\Tools\QcStatsTool;
use App\Mcp\Tools\FetchRecordsTool;
use App\Mcp\Tools\AggregateRecordsTool;
use App\Mcp\Tools\AnomalyDetectorTool;
use App\Mcp\Tools\TopPartnersTool;
use App\Mcp\Tools\PayrollAssistantTool;
use App\Mcp\Tools\AutoReconcileProportionalTool;
use App\Mcp\Tools\BatchReconcileByEmailTool;
use App\Mcp\Tools\RevertReconcileByEmailTool;
use App\Mcp\Tools\BatchApproveReportsTool;
use App\Mcp\Tools\CreateWorkerTool;
use App\Mcp\Tools\CreateCustomUserTool;
use App\Mcp\Tools\SoftDeleteEmailTool;
use App\Mcp\Tools\SendWaTool;

class McpServerController extends Controller
{
    protected McpToolRegistry $registry;

    public function __construct()
    {
        $this->registry = new McpToolRegistry();
        
        // Register all tools
        $this->registry->register(new SearchPartnerTool());
        $this->registry->register(new QcStatsTool());
        $this->registry->register(new FetchRecordsTool());
        $this->registry->register(new AggregateRecordsTool());
        $this->registry->register(new AnomalyDetectorTool());
        $this->registry->register(new TopPartnersTool());
        $this->registry->register(new PayrollAssistantTool());
        $this->registry->register(new AutoReconcileProportionalTool());
        $this->registry->register(new BatchReconcileByEmailTool());
        $this->registry->register(new RevertReconcileByEmailTool());
        $this->registry->register(new BatchApproveReportsTool());
        $this->registry->register(new CreateWorkerTool());
        $this->registry->register(new CreateCustomUserTool());
        $this->registry->register(new SoftDeleteEmailTool());
        $this->registry->register(new SendWaTool());
    }

    public function handle(Request $request)
    {
        // $request->attributes->get('mcp_client') diisi oleh McpAuthenticate middleware
        $client = $request->attributes->get('mcp_client');

        $method = $request->input('method');
        $id = $request->input('id');

        if (!$method) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32600,
                    'message' => 'Missing method parameter'
                ]
            ], 400);
        }

        try {
            if ($method === 'initialize') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => [
                            'tools' => (object)[]
                        ],
                        'serverInfo' => [
                            'name' => 'kamerakita-mcp-secure',
                            'version' => '2.0.0'
                        ]
                    ]
                ]);
            }

            if ($method === 'notifications/initialized') {
                return response()->json(); // No response needed for notification
            }

            if ($method === 'tools/list') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'tools' => $this->registry->getRegisteredToolsFormatted($client)
                    ]
                ]);
            }

            if ($method === 'tools/call') {
                $toolName = $request->input('params.name');
                $args = $request->input('params.arguments', []);
                $isSimulation = $request->input('params.arguments.is_simulation', false) || $request->input('is_simulation', false);

                if (!$toolName) {
                    throw new \Exception("Missing tool name in params.name");
                }

                // Temporary backward compatibility wrapper for execute_action
                if ($toolName === 'execute_action') {
                    $action = $args['action'] ?? null;
                    $payload = $args['payload'] ?? [];
                    if (in_array($action, ['delete', 'destroy', 'remove', 'forceDelete'])) {
                        throw new \Exception("NO HARD DELETES RULE: Permintaan destruktif diblokir secara absolut oleh MCP Server.");
                    }
                    $toolMap = [
                        'batch_approve_reports' => 'batch_approve_reports',
                        'soft_delete_email' => 'soft_delete_email',
                        'create_worker' => 'create_worker',
                        'create_custom_user' => 'create_custom_user',
                    ];
                    if (isset($toolMap[$action])) {
                        $toolName = $toolMap[$action];
                        $args = $payload; // Forward payload as arguments
                    } else {
                        throw new \Exception("Aksi tidak didukung dalam execute_action: {$action}");
                    }
                }

                if ($isSimulation) {
                    DB::beginTransaction();
                }

                // Execute via registry
                $result = $this->registry->execute($toolName, $args, $client);

                if ($isSimulation) {
                    DB::rollBack();
                    $result = [
                        'simulation_status' => 'success',
                        'message' => 'Dry-run successful. No data was actually modified.',
                        'preview' => $result
                    ];
                }

                $jsonString = is_array($result) || is_object($result) ? json_encode($result) : (string)$result;
                
                return response()->json([
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $jsonString
                            ]
                        ],
                        'isError' => false
                    ]
                ]);
            }

            throw new \Exception("Unsupported method: {$method}");

        } catch (\Exception $e) {
            if (isset($isSimulation) && $isSimulation) {
                DB::rollBack();
            }
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $request->input('id'),
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
