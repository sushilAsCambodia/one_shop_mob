<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Deal;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product_deals = [
            [
                'product_id' => 1,
                'slot_id' => 1,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 2,
                'slot_id' => 2,
                'slot_price' => 1,
                'deal_price' => 80,
                'time_period' => 50,
            ],
            [
                'product_id' => 3,
                'slot_id' => 4,
                'slot_price' => 1,
                'deal_price' => 70,
                'time_period' => 40,
            ],
            [
                'product_id' => 4,
                'slot_id' => 4,
                'slot_price' => 1,
                'deal_price' => 80,
                'time_period' => 40,
            ],
            [
                'product_id' => 5,
                'slot_id' => 5,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 50,
            ],
            [
                'product_id' => 6,
                'slot_id' => 6,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 7,
                'slot_id' => 7,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 8,
                'slot_id' => 8,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 50,
            ],
            [
                'product_id' => 9,
                'slot_id' => 9,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 10,
                'slot_id' => 10,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 11,
                'slot_id' => 11,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 50,
            ],
            [
                'product_id' => 12,
                'slot_id' => 12,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 13,
                'slot_id' => 13,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 50,
            ],
            [
                'product_id' => 14,
                'slot_id' => 14,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 40,
            ],
            [
                'product_id' => 15,
                'slot_id' => 15,
                'slot_price' => 1,
                'deal_price' => 90,
                'time_period' => 90,
            ],

        ];
        foreach ($product_deals as $product_deal) {
            // filters
            Deal::updateOrcreate($product_deal);
        }
    }
}
