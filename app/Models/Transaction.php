<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'user_id', // Memastikan user_id bisa diisi otomatis
        'date_transaction',
        'amount',
        'note',
        'image'
    ];

    // Menetapkan user_id otomatis berdasarkan user yang sedang login
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (auth()->check()) {
                $transaction->user_id = auth()->id(); // Mengisi user_id sesuai user yang login
            }
        });
    }

    // Relasi ke Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk transaksi pengeluaran (expense)
    public function scopeExpenses($query)
    {
        return $query->whereHas('category', function ($query) {
            $query->where('is_expense', true);
        });
    }

    // Scope untuk transaksi pemasukan (income)
    public function scopeIncomes($query)
    {
        return $query->whereHas('category', function ($query) {
            $query->where('is_expense', false);
        });
    }
}
