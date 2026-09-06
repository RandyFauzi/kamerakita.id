<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    /**
     * Switch the application locale.
     */
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|in:id,en'
        ]);

        $locale = $validated['locale'];

        // If authenticated, persist to user profile
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Persist to session
        $request->session()->put('locale', $locale);

        // Safely redirect back. Cookie will be set by the SetLocale middleware on next GET request.
        return redirect()->back();
    }
}
