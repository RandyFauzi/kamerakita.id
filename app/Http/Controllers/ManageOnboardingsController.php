<?php

namespace App\Http\Controllers;

use App\Models\FastworkOnboarding;
use Illuminate\Http\Request;

class ManageOnboardingsController extends Controller
{
    public function index(Request $request)
    {
        $query = FastworkOnboarding::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('whatsapp_number', 'like', "%{$search}%")
                  ->orWhere('fastwork_username', 'like', "%{$search}%")
                  ->orWhere('device_type', 'like', "%{$search}%");
            });
        }

        $onboardings = $query->latest()->paginate(15)->withQueryString();

        return view('onboardings.index', compact('onboardings'));
    }

    public function destroy(FastworkOnboarding $onboarding)
    {
        $onboarding->delete();
        return redirect()->route('admin.onboardings.index')->with('success', 'Data pendaftar Fastwork berhasil dihapus.');
    }
}
