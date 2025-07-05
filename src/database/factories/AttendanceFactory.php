<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'work_date' => $this->faker->date('Y-m-d'),
            'clock_in' => $this->faker->time('H:i:s', 'now'),
            'clock_out' => $this->faker->time('H:i:s', 'now'),
            'status' => '退勤済',
        ];
    }
}
