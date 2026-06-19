<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function dailyRevenue(?Carbon $date = null): float
    {
        $date = $date ?? now();

        return (float) Order::query()
            ->where('status', OrderStatus::Closed)
            ->whereDate('updated_at', $date)
            ->sum('total');
    }

    public function openOrderCount(): int
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::Open, OrderStatus::Sent])
            ->count();
    }

    public function topProducts(int $limit = 10, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now()->endOfDay();

        return OrderItem::query()
            ->select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(qty * unit_price) as total_revenue'))
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', OrderStatus::Closed)
                    ->whereBetween('updated_at', [$from, $to]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->with('product')
            ->get();
    }
}
