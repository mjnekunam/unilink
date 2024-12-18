<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalendarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'frequency' => 'string',
            'description' => 'nullable|string|max:1000',
            'custom.interval' => ['required', 'integer', 'between:1,52'],
            'custom.noEndDate' => ['required', 'boolean'],
            'custom.startDate' => 'required',
            "custom.endDate" => 'required',
            'dates.*.date' => 'required',
            'dates.*.times.*.start' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):([0-5]?[0-9])$/'],
            'dates.*.times.*.end' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):([0-5]?[0-9])$/', 'after:dates.*.times.*.start'],
            'dates.*.times.*.rrule' => 'nullable|string',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'لطفا عنوان را پر کنید.',
            'dates.*.times.*.start.required' => 'لطفا ساعت شروع را پر کنید.',
            'dates.*.times.*.end.required' => 'لطفا ساعت پایان را پر کنید.',
            'dates.*.times.*.start.regex' => 'قالب ساعت درست نیست (مثال:‌ ۱۲:۱۰).',
            'dates.*.times.*.end.regex' => 'قالب ساعت ساعت درست نیست (مثال:‌ ۱۲:۱۰).',
            'dates.*.times.*.end.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
            'dates.*.date.required' => 'تاریخ را پر کنید.',
            'custom.startDate' => 'تاریخ را پر کنید.',
            'custom.endDate' => 'تاریخ را پر کنید.',
            'custom.interval.required' => 'لطفا مقدار را پر کنید.',
            'custom.interval.between' => 'مقدار این فیلد باید بین ۱ تا ۵۲ باشد.',
        ];
    }
}
