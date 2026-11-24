<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center space-y-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                Kamerakita MCP &middot; OAuth 2.1
            </span>
            <h1 class="text-xl font-bold text-slate-900">Permintaan Akses Asisten AI</h1>
            <p class="text-sm text-slate-600">
                <strong>{{ $client->name }}</strong> meminta izin untuk mengakses data &amp; tool Kamerakita atas nama
                <strong>{{ $user->name }}</strong> ({{ $user->email }}).
            </p>
        </div>

        @if (count($scopes) > 0)
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin yang Diminta</div>
                <ul class="space-y-1.5 text-sm text-slate-700">
                    @foreach ($scopes as $scope)
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            <span>{{ $scope->description }} <code class="text-xs text-slate-400">({{ $scope->id }})</code></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                    Izinkan Akses
                </button>
            </form>

            <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
                    Tolak
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
