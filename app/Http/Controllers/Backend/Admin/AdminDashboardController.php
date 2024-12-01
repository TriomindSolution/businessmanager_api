<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    use ResponseTrait;
    public function adminDashboardInformation(Request $request)
    {

        $data = [];
        $largeCards = [];
        $totalRevenueAmount = 0;
        $totalOrderCount = Order::count();
        $cancelOrderCount = Order::Cancel()->count();
        $deliveredOrderCount = Order::Paid()->count();
        $pendingOrderCount = Order::Pending()->count();

        $topCustomers = DB::table('customers')
            ->join('orders', 'customers.phone', '=', 'orders.customer_phone')
            ->select('customers.phone', 'customers.name', DB::raw('COUNT(orders.id) as order_count'))
            ->where('orders.payment', 1)
            ->where('orders.order_status', 4)
            ->groupBy('customers.phone', 'customers.name')
            ->orderByDesc('order_count')
            ->get();

        $topSellingProducts = DB::table('order_products')
            ->select('products.id', 'products.name', DB::raw('COUNT(order_products.product_id) as total_orders'))
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->groupBy('order_products.product_id', 'products.id', 'products.name')
            ->orderBy('total_orders', 'desc')
            ->get();

        $totalRevenue['totalRevenue'] = $totalRevenueAmount;
        $inProgressConsultation['totalOrder'] = $totalOrderCount;
        $cancelOrder['cancelOrder'] = $cancelOrderCount;
        $paidOrder['paidOrder'] = $deliveredOrderCount;
        $pendingOrder['pendingOrder'] = $pendingOrderCount;

        $largeCards = [$inProgressConsultation, $cancelOrder, $paidOrder, $totalRevenue, $pendingOrder];

        $data['firstLayer'] = $largeCards;

        $data['topPurchasingCustomer'] = $topCustomers;

        $data['topSellingProducts'] = $topSellingProducts;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function getOrderStats(Request $request)
    {
        $type = $request->input('type');
        $groupBy = match ($type) {
            '1' => "%Y-%m-%d",
            '2' => "%Y-%m",
            '3' => "%Y",
            default => "%Y-%m",
        };

        $periods = [];
        $paidOrder = [];
        $cancelOrder = [];

        if ($type == '1') {
            $periods = collect(range(0, 29))->map(fn($i) => date('Y-m-d', strtotime("-$i days")))->reverse()->values();
        } elseif ($type == '2') {
            $periods = collect(range(0, 11))->map(fn($i) => date('Y-m', strtotime("-$i months")))->reverse()->values();
        } elseif ($type == '3') {
            $periods = collect(range(0, 4))->map(fn($i) => date('Y', strtotime("-$i years")))->reverse()->values();
        }

        $paid = DB::table('orders')
            ->where('payment', 1)
            ->where('order_status', 4)
            ->selectRaw("DATE_FORMAT(updated_at, '$groupBy') as period, COUNT(*) as complete_count")
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->get()
            ->keyBy('period');

        $cancel = DB::table('orders')
            ->where('payment', 4)
            ->where('order_status', 5)
            ->selectRaw("DATE_FORMAT(updated_at, '$groupBy') as period, COUNT(*) as cancel_count")
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->get()
            ->keyBy('period');

        foreach ($periods as $period) {
            $paidOrder[] = $paid[$period]->complete_count ?? 0;
            $cancelOrder[] = $cancel[$period]->cancel_count ?? 0;
        }

        $data = [
            "orderStatistics" => [
                [
                    $type == '1' ? "days" : ($type == '2' ? "month" : "year") => $periods,
                    "paidOrder" => $paidOrder,
                    "cancelOrder" => $cancelOrder,
                ],
            ],
        ];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function getOldNewCustomer(Request $request)
    {
        $type = $request->input('type');
        $groupBy = match ($type) {
            '1' => "%Y-%m-%d", // by day
            '2' => "%Y-%m",    // by month
            '3' => "%Y",       // by year
            default => "%Y-%m", // Default
        };

        // Define periods based on the type
        $periods = match ($type) {
            '1' => collect(range(0, 29))->map(fn($i) => date('Y-m-d', strtotime("-$i days")))->reverse()->values(),
            '2' => collect(range(0, 11))->map(fn($i) => date('Y-m', strtotime("-$i months")))->reverse()->values(),
            '3' => collect(range(0, 4))->map(fn($i) => date('Y', strtotime("-$i years")))->reverse()->values(),
            default => [],
        };

        // Fetch all customers grouped by their periods
        $customers = DB::table('orders')
            ->where('payment', 1)
            ->where('order_status', 4)
            ->selectRaw("customer_phone, DATE_FORMAT(updated_at, '$groupBy') as period, MIN(DATE_FORMAT(updated_at, '$groupBy')) as first_order_period")
            ->groupBy('customer_phone', 'period')
            ->get();

        // Initialize results
        $newCustomers = [];
        $oldCustomers = [];

        foreach ($periods as $period) {
            $newCount = $customers
                ->where('period', $period)
                ->filter(fn($customer) => $customer->first_order_period === $period)
                ->count();

            $oldCount = $customers
                ->where('period', $period)
                ->filter(fn($customer) => $customer->first_order_period < $period)
                ->count();

            $newCustomers[] = $newCount;
            $oldCustomers[] = $oldCount;
        }

        // Prepare response
        $data = [
            "customerStatistics" => [
                [
                    $type == '1' ? "days" : ($type == '2' ? "month" : "year") => $periods,
                    "newCustomers" => $newCustomers,
                    "oldCustomers" => $oldCustomers,
                ],
            ],
        ];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }


}
