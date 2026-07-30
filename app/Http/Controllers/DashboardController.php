<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        return view('dashboard', [
            'todayRevenue' => $this->revenueBetween($today, $today),
            'monthRevenue' => $this->revenueBetween($today->copy()->startOfMonth(), $today),
            'totalRevenue' => (float) Sale::sum('total_amount'),

            'outstanding' => (float) Sale::whereIn('payment_status', [
                PaymentStatus::Unpaid->value,
                PaymentStatus::Partial->value,
            ])->sum(DB::raw('total_amount - amount_paid')),

            'todayEntries' => Sale::whereDate('sale_date', $today)->count(),

            'recentSales' => Sale::with(['product:id,name,unit', 'dealer:id,name'])
                ->latest('sale_date')
                ->latest('id')
                ->limit(8)
                ->get(),

            'topProducts' => Sale::query()
                ->selectRaw('product_id, SUM(total_amount) as revenue, SUM(quantity) as quantity')
                ->with('product:id,name,unit')
                ->whereBetween('sale_date', [$today->copy()->startOfMonth(), $today])
                ->groupBy('product_id')
                ->orderByDesc('revenue')
                ->limit(5)
                ->get(),
        ]);
    }

    private function revenueBetween(Carbon $from, Carbon $to): float
    {
        return (float) Sale::whereBetween('sale_date', [$from, $to])->sum('total_amount');
    }
}
