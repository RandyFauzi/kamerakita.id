$request = request();
$selectedPeriodKey = null;

$range = App\Services\PeriodService::getPeriodRange(now());
$startDate = $range['start'];
$endDate = $range['end'];
$filterDate = null;
$stripStart = $startDate;
$stripEnd = $endDate;

$periodDays = [];
if ($stripStart && $stripEnd) {
    $day = $stripStart->copy();
    while ($day->lte($stripEnd)) {
        $periodDays[] = $day->copy();
        $day->addDay();
    }
}

$search = null;
$selectedGroup = null;
$query = App\Models\Partner::whereHas('videoWorkReports', function ($q) use ($startDate, $endDate, $filterDate) {
    if ($filterDate) {
        $q->whereDate('submission_date', $filterDate->format('Y-m-d'));
    } elseif ($startDate && $endDate) {
        $q->whereBetween('submission_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
    }
});

$partners = $query->paginate(15);
echo "DONE PAGINATE\n";
