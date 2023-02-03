<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Order;
use App\Models\TimeInterval;
use App\Services\OrderProductService;
use App\Services\SlotDealService;
use Illuminate\Http\Request;

class DemoController extends Controller
{

    public function demoWork()
    {
        dd('Sushil');
        $orderProductService = new OrderProductService();
        $dealsData = Deal::where('status', 'active')
            ->join('slots', 'slots.id', '=', 'deals.slot_id')
            ->select(
                'slots.id as slot_id',
                'deals.id as deal_id',
                'deals.product_id',
                'slots.total_slots',
                'slots.booked_slots',
                'deals.created_at as start_date',
                'deals.time_period',
                'deals.deal_end_at'
            )->get();

        foreach ($dealsData as $dealsDataVal) {
            $timeDuration   = $dealsDataVal->time_period;                  // in hour
            $numberOfSlots  = $dealsDataVal->total_slots;

            $slotsBook = $dealsDataVal->booked_slots;
            $throughput = round($numberOfSlots / $timeDuration);           // 1 hour full fill need

            $interValTime = TimeInterval::where('deal_id', $dealsDataVal->deal_id)->where('status', 'pending')->orderBy('id', 'asc')->first();

            $newDateTime = date("Y-m-d H:i:s", strtotime("$dealsDataVal->start_date + $interValTime->interval_time hours"));

            $currentDate = date('Y-m-d H:i:s');

            if ($currentDate >= $newDateTime) {

                if ($numberOfSlots == $slotsBook) {
                    // echo 'Slots Full';
                } else if ($slotsBook >= $throughput * $interValTime->interval_time) {
                    // echo 'No need Bot';
                } else {
                    $insertSlots = ($throughput * $interValTime->interval_time) - $slotsBook; // Bot insertion

                    // Bot Setting..
                    $custmerIdBot = 6;
                    $dataBot      = array();
                    $dataBot      = [
                        "total_amount" => $insertSlots,
                        "total_slots" => $insertSlots,
                        "total_products" => $insertSlots,
                        "total_quantity" => $insertSlots
                    ];
                    $dataBot['product_details'] = [["product_id" => $dealsDataVal->product_id, "amount" => $insertSlots, "slots" => $insertSlots,],];

                    $orderIdBot = Order::create(
                        array_merge($dataBot, array('customer_id' => $custmerIdBot, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'confirmed'))
                    )->id;

                    $orderProductService->store($dataBot['product_details'], $custmerIdBot, $orderIdBot, $isBot = 1);
                    $statusChange['status'] = 'done';
                    $interValTime->update($statusChange);
                }
            }
        }
    }

    public function addIntervalDemoWork()
    {
        // $timeDuration   = 25;                     // in hour
        // $randonInterval = array();

        // for ($x = 1; $x <= $timeDuration; $x++) {

        //   $start_date = "2023-01-12 11:57:26";
        //   $interval_time = $x * 60;

        //   $newDateTime = date("Y-m-d H:i:s", strtotime("$start_date + $interval_time minutes"));

        //   array_push($randonInterval, $this->randomDate($start_date, $newDateTime));

        // }
        // print_r($randonInterval);
        // die;
        // $dealsData = Deal::where('status', 'active')
        //     ->join('slots', 'slots.id', '=', 'deals.slot_id')
        //     ->select(
        //         'slots.id as slot_id',
        //         'deals.id as deal_id',
        //         'slots.total_slots',
        //         'slots.booked_slots',
        //         'deals.created_at',
        //         'deals.time_period',
        //         'deals.deal_end_at'
        //     )
        //     ->get();

        $dealsData = Deal::where('status', 'active')->get();

        foreach ($dealsData as $dealsDataVal) {
            $timeDuration   = $dealsDataVal->time_period;
            $start_date = $dealsDataVal->created_at;                                       // in hour
            $randonInterval = array();

            for ($x = 1; $x <= $timeDuration; $x++) {

                $interval_time = $x * 60;

                $newDateTime = date("Y-m-d H:i:s", strtotime("$start_date + $interval_time minutes"));
                $data = $this->randomDate($start_date, $newDateTime);
                array_push($randonInterval, $data);

                $start_date = $newDateTime;

                TimeInterval::create([
                    'deal_id'      => $dealsDataVal->id,
                    'interval_time' => $data,
                ]);
            }
        }
    }
    function randomDate($start_date, $end_date)
    {
        $min = strtotime($start_date);
        $max = strtotime($end_date);
        $val = rand($min, $max);
        return date('Y-m-d H:i:s', $val);
    }
}
