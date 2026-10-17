{{-- Share the admin recording presentation; the controller supplies team-scoped data. --}}
@include('admin.atlas-recordings.index', [
    'isVendorView' => true,
    'recordings' => $records,
    'stats' => [
        'total_worked' => $stats->worked,
        'total_approved' => $stats->approved,
        'total_review' => $stats->pending,
        'total_rejected' => $stats->rejected,
        'total_deferred' => $stats->deferred,
    ],
])
