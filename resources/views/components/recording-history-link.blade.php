@props(['partner'])
<a href="{{ route('admin.recordings.index', ['partner_id' => $partner->id]) }}"
   title="Lihat riwayat recording {{ $partner->full_name }}" aria-label="Lihat riwayat recording {{ $partner->full_name }}"
   class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg text-gray-500 transition hover:bg-indigo-50 hover:text-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
</a>
