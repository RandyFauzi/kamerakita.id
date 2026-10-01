<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Update user's push subscription.
     */
    public function update(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);
        try {
            $endpoint = $request->endpoint;
            $token = $request->keys['auth'];
            $key = $request->keys['p256dh'];
            $contentEncoding = $request->contentEncoding;

            $user = $request->user();
            
            $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

            return response()->json(['success' => true], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Delete user's push subscription.
     */
    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required']);

        $request->user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true], 200);
    }
}
