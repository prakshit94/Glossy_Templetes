<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Refund;
use App\Models\Party;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'today');
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | DATE FILTERS
        |--------------------------------------------------------------------------
        */
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

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | BASE QUURIES (Role Based)
        |--------------------------------------------------------------------------
        */
        $orderQuery = Order::query();
        $returnsQuery = OrderReturn::query();

        // Security: Filter by owner if not Admin
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if (Schema::hasColumn('orders', 'created_by')) {
                $orderQuery->where('created_by', $user->id);
            }
            if (Schema::hasColumn('order_returns', 'created_by')) {
                $returnsQuery->where('created_by', $user->id);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | HELPER: PERCENTAGE CHANGE
        |--------------------------------------------------------------------------
        */
        $calcPercent = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        /*
        |--------------------------------------------------------------------------
        | CURRENT METRICS
        |--------------------------------------------------------------------------
        */
        $revenue = (clone $orderQuery)
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('net_amount');

        $ordersCount = (clone $orderQuery)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->count();

        $cancelledOrdersCount = (clone $orderQuery)
            ->where('status', 'cancelled')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->count();

        $newCustomers = Party::where('type', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $refundsAmount = Refund::where('status', 'processed')
            ->whereBetween('processed_at', [$startDate, $endDate])
            ->sum('amount');

        $activeReturns = (clone $returnsQuery)
            ->whereIn('status', ['requested', 'received', 'inspected'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | PREVIOUS METRICS (For Comparison)
        |--------------------------------------------------------------------------
        */
        $prevRevenue = (clone $orderQuery)
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereBetween('order_date', [$prevStartDate, $prevEndDate])
            ->sum('net_amount');

        $prevOrdersCount = (clone $orderQuery)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('order_date', [$prevStartDate, $prevEndDate])
            ->count();

        $prevCancelledOrdersCount = (clone $orderQuery)
            ->where('status', 'cancelled')
            ->whereBetween('order_date', [$prevStartDate, $prevEndDate])
            ->count();

        $prevNewCustomers = Party::where('type', 'customer')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevRefundsAmount = Refund::where('status', 'processed')
            ->whereBetween('processed_at', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $prevActiveReturns = (clone $returnsQuery)
            ->whereIn('status', ['requested', 'received', 'inspected'])
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $diffs = [
            'revenue'   => $calcPercent($revenue, $prevRevenue),
            'orders'    => $calcPercent($ordersCount, $prevOrdersCount),
            'cancelled' => $calcPercent($cancelledOrdersCount, $prevCancelledOrdersCount),
            'customers' => $calcPercent($newCustomers, $prevNewCustomers),
            'refunds'   => $calcPercent($refundsAmount, $prevRefundsAmount),
            'returns'   => $calcPercent($activeReturns, $prevActiveReturns),
        ];

        /*
        |--------------------------------------------------------------------------
        | CHART DATA GENERATION
        |--------------------------------------------------------------------------
        */
        $chartLabels = [];
        $salesData = [];
        $ordersData = [];

        $daysDiff = $startDate->diffInDays($endDate);

        if ($daysDiff <= 1) { // Hourly breakdown
            $payments = (clone $orderQuery)
                ->whereNotIn('status', ['cancelled', 'returned'])
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('HOUR(order_date) as hour'), DB::raw('SUM(net_amount) as total'))
                ->groupBy('hour')->get()->pluck('total', 'hour')->toArray();

            $ordersGrouped = (clone $orderQuery)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('HOUR(order_date) as hour'), DB::raw('COUNT(*) as total'))
                ->groupBy('hour')->get()->pluck('total', 'hour')->toArray();

            for ($i = 0; $i < 24; $i++) {
                $chartLabels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $salesData[] = $payments[$i] ?? 0;
                $ordersData[] = $ordersGrouped[$i] ?? 0;
            }
        } elseif ($daysDiff <= 31) { // Daily breakdown
            $payments = (clone $orderQuery)
                ->whereNotIn('status', ['cancelled', 'returned'])
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(order_date) as date'), DB::raw('SUM(net_amount) as total'))
                ->groupBy('date')->get()->pluck('total', 'date')->toArray();

            $ordersGrouped = (clone $orderQuery)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('DATE(order_date) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')->get()->pluck('total', 'date')->toArray();

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $chartLabels[] = $current->format('M d');
                $salesData[] = $payments[$dateStr] ?? 0;
                $ordersData[] = $ordersGrouped[$dateStr] ?? 0;
                $current->addDay();
            }
        } else { // Monthly breakdown
            $payments = (clone $orderQuery)
                ->whereNotIn('status', ['cancelled', 'returned'])
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(order_date) as month'), DB::raw('SUM(net_amount) as total'))
                ->groupBy('month')->get()->pluck('total', 'month')->toArray();

            $ordersGrouped = (clone $orderQuery)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('order_date', [$startDate, $endDate])
                ->select(DB::raw('MONTH(order_date) as month'), DB::raw('COUNT(*) as total'))
                ->groupBy('month')->get()->pluck('total', 'month')->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $chartLabels[] = Carbon::create()->month($i)->format('M');
                $salesData[] = $payments[$i] ?? 0;
                $ordersData[] = $ordersGrouped[$i] ?? 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RECENT ACTIVITY
        |--------------------------------------------------------------------------
        */
        $recentOrders = (clone $orderQuery)->with('party')->latest()->take(5)->get();
        $recentReturns = (clone $returnsQuery)->with('order')->latest()->take(5)->get();
        
        $recentReviews = DB::table('product_reviews')
            ->join('products', 'product_reviews.product_id', '=', 'products.id')
            ->where('product_reviews.user_id', Auth::id())
            ->select([
                'product_reviews.id',
                'product_reviews.rating',
                'product_reviews.comment',
                'product_reviews.status',
                'product_reviews.created_at',
                'products.name as product_name',
            ])
            ->orderByDesc('product_reviews.created_at')
            ->limit(5)
            ->get();

        $dateRangeString = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

        /*
        |--------------------------------------------------------------------------
        | AJAX & VIEW RESPONSE
        |--------------------------------------------------------------------------
        */
        $metricsView = view('dashboard.partials.metrics', compact(
            'revenue', 'ordersCount', 'cancelledOrdersCount', 'newCustomers', 'refundsAmount', 'activeReturns', 'diffs'
        ))->render();

        if ($request->ajax()) {
            return response()->json([
                'chartLabels'     => $chartLabels,
                'salesData'       => $salesData,
                'ordersData'      => $ordersData,
                'dateRangeString' => $dateRangeString,
                'html'            => $metricsView
            ]);
        }

        return view('dashboard', compact(
            'filter',
            'revenue', 'ordersCount', 'cancelledOrdersCount', 'newCustomers', 'refundsAmount', 'activeReturns', 'diffs',
            'chartLabels', 'salesData', 'ordersData', 'recentOrders', 'recentReturns', 'recentReviews', 'dateRangeString'
        ));
    }
}