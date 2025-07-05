<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
            'note'      => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'    => '出勤時間を入力してください。',
            'clock_out.required'   => '退勤時間を入力してください。',
            'note.required'        => '備考を記入してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn    = $this->input('clock_in');
            $clockOut   = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            // 条件 1: 出勤 > 退勤 もしくは 退勤 < 出勤
            if ($clockIn && $clockOut && strtotime($clockIn) > strtotime($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 条件 2: 休憩が勤務時間外にある
            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;

                if ($start && $clockIn && strtotime($start) < strtotime($clockIn)) {
                    $validator->errors()->add("breaks.$index.start", '休憩時間が勤務時間外です');
                }

                if ($end && $clockOut && strtotime($end) > strtotime($clockOut)) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間が勤務時間外です');
                }
            }
        });
    }

}
