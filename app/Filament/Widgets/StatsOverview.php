<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth; // Tambahkan ini untuk mendapatkan user yang login

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected function getStats(): array
    {
        $startDate = ! is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            null;

        $endDate = ! is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            now();

        // Ambil user_id yang sedang login
        $userId = Auth::id();

        // Hanya ambil transaksi berdasarkan user yang login
        $pemasukan = Transaction::where('user_id', $userId) // Tambahkan filter user_id
                        ->incomes()
                        ->whereBetween('date_transaction', [$startDate, $endDate])
                        ->sum('amount');

        $pengeluaran = Transaction::where('user_id', $userId) // Tambahkan filter user_id
                        ->expenses()
                        ->whereBetween('date_transaction', [$startDate, $endDate])
                        ->sum('amount');
        
        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($pemasukan, 0, ',', '.')),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($pengeluaran, 0, ',', '.')),
            Stat::make('Selisih', 'Rp ' . number_format($pemasukan - $pengeluaran, 0, ',', '.')),
        ];
    }
}
