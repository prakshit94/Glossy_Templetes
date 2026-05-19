<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderReturn;
use App\Models\Refund;
use App\Models\Party;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'today'); // today, yesterday, this_week, this_month, this_year, previous_year

        $now = Carbon::now();
        switch ($filter) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $prevStartDate = $now->copy()->subDay()->startOfDay();
                $prevEndDate = $now->copy()->subDay()->endOfDay();
                break;
            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay();
                $endDate = $now->copy()->subDay()->endOfDay();
                $prevStartDate = $now->copy()->subDays(2)->startOfDay();
                $prevEndDate = $now->copy()->subDays(2)->endOfDay();
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $prevStartDate = $now->copy()->subWeek()->startOfWeek();
                $prevEndDate = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $prevStartDate = $now->copy()->subYear()->startOfYear();
                $prevEndDate = $now->copy()->subYear()->endOfYear();
                break;
            case 'previous_year':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate = $now->copy()->subYear()->endOfYear();
                $prevStartDate = $now->copy()->subYears(2)->startOfYear();
                $prevEndDate = $now->copy()->subYears(2)->endOfYear();
                break;
            case 'this_month':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonth()->startOfMonth();
                $prevEndDate = $now->copy()->subMonth()->endOfMonth();
                break;
        }

        // --- Helper for % Change ---
        $calcPercent = function($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        // --- Current Metrics ---
        $revenue = Payment::where('status', 'completed')->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $ordersCount = Order::where('status', '!=', 'cancelled')->whereBetween('order_date', [$startDate, $endDate])->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->whereBetween('order_date', [$startDate, $endDate])->count();
        $newCustomers = Party::where('type', 'customer')->whereBetween('created_at', [$startDate, $endDate])->count();
        $refundsAmount = Refund::where('status', 'processed')->whereBetween('processed_at', [$startDate, $endDate])->sum('amount');
        $activeReturns = OrderReturn::whereIn('status', ['requested', 'received', 'inspected'])->whereBetween('created_at', [$startDate, $endDate])->count();

        // --- Previous Metrics ---
        $prevRevenue = Payment::where('status', 'completed')->whereBetween('payment_date', [$prevStartDate, $prevEndDate])->sum('amount');
        $prevOrdersCount = Order::where('status', '!=', 'cancelled')->whereBetween('order_date', [$prevStartDate, $prevEndDate])->count();
        $prevCancelledOrdersCount = Order::where('status', 'cancelled')->whereBetween('order_date', [$prevStartDate, $prevEndDate])->count();
        $prevNewCustomers = Party::where('type', 'customer')->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $prevRefundsAmount = Refund::where('status', 'processed')->whereBetween('processed_at', [$prevStartDate, $prevEndDate])->sum('amount');
        $prevActiveReturns = OrderReturn::whereIn('status', ['requested', 'received', 'inspected'])->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();

        // --- Percentages ---
        $diffs = [
            'revenue' => $calcPercent($revenue, $prevRevenue),
            'orders' => $calcPercent($ordersCount, $prevOrdersCount),
            'cancelled' => $calcPercent($cancelledOrdersCount, $prevCancelledOrdersCount),
            'customers' => $calcPercent($newCustomers, $prevNewCustomers),
            'refunds' => $calcPercent($refundsAmount, $prevRefundsAmount),
            'returns' => $calcPercent($activeReturns, $prevActiveReturns),
        ];

        // --- Chart Data: Sales & Orders over the period ---
        $chartData = [];
        $chartLabels = [];
        $salesData = [];
        $ordersData = [];

        $daysDiff = $startDate->diffInDays($endDate);

        if ($daysDiff <= 1) { // Today / Yesterday - Group by Hour
            $payments = Payment::where('status', 'completed')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->select(DB::raw('HOUR(payment_date) as hour'), DB::raw('SUM(amount) as total'))
                ->groupBy('hour')
                ->pluck('total', 'hour')->toArray();
                
            $ordersGrouped = Order::where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('HOUR(order_date) as hour'), DB::raw('COUNT(*) as total'))
                ->groupBy('hour')
                ->pluck('total', 'hour')->toArray();

            for ($i = 0; $i < 24; $i++) {
                $chartLabels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $salesData[] = $payments[$i] ?? 0;
                $ordersData[] = $ordersGrouped[$i] ?? 0;
            }
        } elseif ($daysDiff <= 31) { // Week / Month - Group by Day
            $payments = Payment::where('status', 'completed')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')->toArray();
                
            $ordersGrouped = Order::where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(order_date) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->pluck('total', 'date')->toArray();

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $chartLabels[] = $current->format('M d');
                $salesData[] = $payments[$dateStr] ?? 0;
                $ordersData[] = $ordersGrouped[$dateStr] ?? 0;
                $current->addDay();
            }
        } else { // Year - Group by Month
            $payments = Payment::where('status', 'completed')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(payment_date) as month'), DB::raw('SUM(amount) as total'))
                ->groupBy('month')
                ->pluck('total', 'month')->toArray();
                
            $ordersGrouped = Order::where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(order_date) as month'), DB::raw('COUNT(*) as total'))
                ->groupBy('month')
                ->pluck('total', 'month')->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $chartLabels[] = date('M', mktime(0, 0, 0, $i, 1));
                $salesData[] = $payments[$i] ?? 0;
                $ordersData[] = $ordersGrouped[$i] ?? 0;
            }
        }

        // Recent Activity
        $recentOrders = Order::with('party')->latest()->take(5)->get();
        $recentReturns = OrderReturn::with('order')->latest()->take(5)->get();

        $dateRangeString = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

        $metricsView = view('dashboard.partials.metrics', compact(
            'revenue', 'ordersCount', 'cancelledOrdersCount', 'newCustomers', 'refundsAmount', 'activeReturns', 'diffs'
        ))->render();

        if ($request->ajax()) {
            return response()->json([
                'chartLabels' => $chartLabels,
                'salesData' => $salesData,
                'ordersData' => $ordersData,
                'dateRangeString' => $dateRangeString,
                'html' => $metricsView
            ]);
        }

        return view('dashboard', compact(
            'filter',
            'revenue', 'ordersCount', 'cancelledOrdersCount', 'newCustomers', 'refundsAmount', 'activeReturns', 'diffs',
            'chartLabels',
            'salesData',
            'ordersData',
            'recentOrders',
            'recentReturns',
            'dateRangeString'
        ));
    }
}
