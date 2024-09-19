<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    use ResponseTrait;
    public function adminDashboardInformation(Request $request)
    {

        $data = [];
        $largeCards = [];
        $competition_paid = [];
        $competition_cancel = [];
        $newRequest = [];
        $completeConsultation = [];
        $consultantScheduleTime = [];
        $newRegisterCitizen = [];
        $competition_final = [];
        $competition_day = [];
        $monthList = [];
        $monthData = [];
        $item = [];
        $item2 = [];
        $monthlyItemAll = [];

        $totalRevenueAmount = 0;
        $totalOrderCount = Order::count();
        $cancelOrderCount = Order::Cancel()->count();
        $deliveredOrderCount = Order::Paid()->count();



        // --------  Start Consultant Performance  ------------

        $paid = DB::table('orders')
            ->where('payment', 1)
            //  ->where(['deleted_at' => null])
            ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
            ->selectRaw("COUNT(updated_at) as paid")
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(updated_at)'), 'ASC')
            ->get()->toArray();
        // return $paid;

        $cancel = DB::table('orders')
            ->where('payment', 4)
            //  ->where(['deleted_at' => null])
            ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
            ->selectRaw("COUNT(updated_at) as cancel")
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(updated_at)'), 'ASC')
            ->get()->toArray();

        //  return $cancel;
        $numberOfMonths = 12;
        $currentMonth = strtotime('now');
        $months = [];

        for ($i = 0; $i < $numberOfMonths; $i++) {
            $completeMonth[] = date('F', $currentMonth);
            $currentMonth = strtotime('last day of previous month', $currentMonth);
        }
        // return $completeMonth;
        foreach ($completeMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($paid as $value) {
                if ($value->month == '01') {
                    $value->month = "January";
                } else if ($value->month == '02') {
                    $value->month = "February";
                } else if ($value->month == '03') {
                    $value->month = "March";
                } else if ($value->month == '04') {
                    $value->month = "April";
                } else if ($value->month == '05') {
                    $value->month = "May";
                } else if ($value->month == '06') {
                    $value->month = "June";
                } else if ($value->month == '07') {
                    $value->month = "July";
                } else if ($value->month == '08') {
                    $value->month = "August";
                } else if ($value->month == '09') {
                    $value->month = "September";
                } else if ($value->month == '10') {
                    $value->month = "October";
                } else if ($value->month == '11') {
                    $value->month = "November";
                } else if ($value->month == '12') {
                    $value->month = "December";
                }
                if ($value->month == $month) {
                    array_push($monthList, $month);
                    array_push($competition_paid, $value->paid);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                array_push($monthList, $month);
                array_push($competition_paid, 0);
            }
        }

        foreach ($completeMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($cancel as $value) {
                if ($value->month == '01') {
                    $value->month = "January";
                } else if ($value->month == '02') {
                    $value->month = "February";
                } else if ($value->month == '03') {
                    $value->month = "March";
                } else if ($value->month == '04') {
                    $value->month = "April";
                } else if ($value->month == '05') {
                    $value->month = "May";
                } else if ($value->month == '06') {
                    $value->month = "June";
                } else if ($value->month == '07') {
                    $value->month = "July";
                } else if ($value->month == '08') {
                    $value->month = "August";
                } else if ($value->month == '09') {
                    $value->month = "September";
                } else if ($value->month == '10') {
                    $value->month = "October";
                } else if ($value->month == '11') {
                    $value->month = "November";
                } else if ($value->month == '12') {
                    $value->month = "December";
                }
                if ($value->month == $month) {
                    // array_push($monthList, $month);
                    array_push($competition_cancel, $value->cancel);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                // array_push($monthList, $month);
                array_push($competition_cancel, 0);
            }
        }

        $topCustomers = Customer::select('customers.id', 'customers.order_id', 'customers.name', 'customers.order_count')
            ->join('orders', 'customers.order_id', '=', 'orders.id')
            ->where('orders.payment', 1)
            ->orderBy('customers.order_count', 'desc')
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



        $item['month'] = $monthList;
        $item['paidOrder'] = $competition_paid;
        $item['cancelOrder'] = $competition_cancel;
        $monthlyItemAll = [$item];

        $largeCards = [$inProgressConsultation, $cancelOrder, $paidOrder, $totalRevenue];

        $data['firstLayer'] = $largeCards;
        $data['orderStatistics'] = $monthlyItemAll;
        $data['topPurchasingCustomer'] = $topCustomers;

        $data['topSellingProducts'] = $topSellingProducts;


        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
}
