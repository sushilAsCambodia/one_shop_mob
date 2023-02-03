<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slot;

class SlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product_slots = [
            [
                'product_id' => 1,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 2,
                'total_slots' => 150,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 3,
                'total_slots' => 160,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 4,
                'total_slots' => 200,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 5,
                'total_slots' => 300,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 6,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 7,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 8,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 9,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 10,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 11,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 12,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 13,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 14,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],
            [
                'product_id' => 15,
                'total_slots' => 100,
                'booked_slots' => 0,
            ],

        ];
        foreach ($product_slots as $product_slot) {
            // filters
            Slot::updateOrcreate($product_slot);
        }
    }
}
