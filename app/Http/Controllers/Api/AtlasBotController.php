<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecordingIngestor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AtlasBotController extends Controller
{
    public function sync(Request $request)
    {
        return $this->ingest($request, 'atlas');
    }

    public function sync1618(Request $request)
    {
        return $this->ingest($request, '1618');
    }

    private function ingest(Request $request, string $source)
    {
        $token = $request->header('X-Bot-Token') ?? $request->input('bot_token');
        $expected = config($source === '1618' ? 'services.robotics1618.bot_token' : 'services.atlas.bot_token');
        if (! $expected || ! is_string($token) || ! hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($source === '1618' && ! Schema::hasColumn('atlas_tasks', 'source_observed_at')) {
            return response()->json(['error' => 'Recording source migration has not been applied.'], 503);
        }

        return response()->json(app(RecordingIngestor::class)->ingest($request->all(), $source));
    }
}
