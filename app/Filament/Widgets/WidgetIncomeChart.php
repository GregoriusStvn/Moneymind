<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;
use App\Models\Transaction;

class WidgetIncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan';
    protected static string $color = 'success';
    use InteractsWithPageFilters;

    protected function getData(): array
    {
        // Pastikan startDate dan endDate tidak null, default ke 7 hari terakhir
        $startDate = isset($this->filters['startDate']) 
            ? Carbon::parse($this->filters['startDate'])->startOfDay()
            : now()->subDays(7)->startOfDay();

        $endDate = isset($this->filters['endDate']) 
            ? Carbon::parse($this->filters['endDate'])->endOfDay()
            : now()->endOfDay();

        // Filter transaksi berdasarkan user yang sedang login
        $data = Transaction::where('user_id', auth()->id()) // Filter transaksi hanya milik user yang login
            ->incomes()
            ->whereBetween('date_transaction', [$startDate, $endDate])
            ->selectRaw('DATE(date_transaction) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Siapkan daftar semua tanggal dalam rentang tersebut
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
                    'label' => 'Pemasukan (Rp)',
                    'data' => array_values($allDates->toArray()),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_keys($allDates->toArray()), // Pastikan semua tanggal muncul
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
