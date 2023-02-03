<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\Configure;
use App\Models\Deal;
use App\Models\Order;
use App\Models\TimeInterval;
use App\Services\OrderProductService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bot = json_decode(Configure::where('type', 'bot')->first()->data);
        if($bot->status != 'active'){
            return false;
        }
        $status = false;
        $orderProductService = new OrderProductService();
        $dealsData = Deal::where('status', 'active')
            ->join('slots', 'slots.id', '=', 'deals.slot_id')
            ->where('deals.is_bot',1)
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

            $interValCount = TimeInterval::where('deal_id', $dealsDataVal->deal_id)->where('status', 'pending')->orderBy('id', 'asc')->count();

            $currentDate = Carbon::now()->format('Y-m-d H:i:s');

            if ($currentDate >= $interValTime->interval_time) {

                $interval_time = strtotime($interValTime->interval_time) - strtotime($dealsDataVal->start_date);

                if ($numberOfSlots == $slotsBook) {
                    // echo 'Slots Full';
                    $status = false;
                } else if ($slotsBook >= $throughput * $interval_time) {
                    // echo 'No need Bot';
                    $status = false;
                } else {
                    $insertSlots = ($throughput * $interval_time) - $slotsBook; // Bot insertion

                    if ($insertSlots + $slotsBook >= $numberOfSlots) {
                        $insertSlots = $numberOfSlots - $slotsBook;
                    }
                    if ($interValCount == 1) {
                        $insertSlots = $numberOfSlots - $slotsBook;
                    }
                    // Bot Setting..
                    $custmerIdBot = 6;
                    $dataBot      = array();
                    $dataBot      = [
                        "total_amount" => $insertSlots, "total_slots" => $insertSlots,
                        "total_products" => $insertSlots, "total_quantity" => $insertSlots
                    ];
                    $dataBot['product_details'] = [["product_id" => $dealsDataVal->product_id, "amount" => $insertSlots, "slots" => $insertSlots,],];

                    $orderIdBot = Order::create(
                        array_merge($dataBot, array('customer_id' => $custmerIdBot, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'confirmed'))
                    )->id;

                    $orderProductService->store($dataBot['product_details'], $custmerIdBot, $orderIdBot, $isBot = 1);
                    $statusChange['status'] = 'done';
                    $interValTime->update($statusChange);

                    $status = true;
                }
            }
        }

        return $status;
    }
}
