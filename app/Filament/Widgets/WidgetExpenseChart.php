<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;
use App\Models\Transaction;

class WidgetExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran';
    protected static string $color = 'danger';
    use InteractsWithPageFilters;

    protected function getData(): array
    {
        $startDate = isset($this->filters['startDate']) 
            ? Carbon::parse($this->filters['startDate'])->startOfDay()
            : now()->subDays(7)->startOfDay();

        $endDate = isset($this->filters['endDate']) 
            ? Carbon::parse($this->filters['endDate'])->endOfDay()
            : now()->endOfDay();

        // Filter transaksi berdasarkan user yang sedang login
        $data = Transaction::where('user_id', auth()->id()) 
            ->expenses()
            ->whereBetween('date_transaction', [$startDate, $endDate])
            ->selectRaw('DATE(date_transaction) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Siapkan daftar tanggal lengkap agar tidak ada yang hilang dalam chart
        $allDates = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $allDates->put($date->format('Y-m-d'), 0);
        }

        // Isi data sesuai tanggal yang tersedia di database
        foreach ($data as $row) {
            $allDates[$row->date] = $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran (Rp)',
                    'data' => array_values($allDates->toArray()),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($allDates->toArray()),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
