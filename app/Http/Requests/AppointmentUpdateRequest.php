<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class AppointmentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        # Teacher
        if ($this->user()->role === 'teacher') {
            $teacherIds = Appointment::whereIn('id', $this->id)->pluck('teacher_id');
            return $teacherIds->every(fn($teacherId) => $teacherId === $this->user()->id);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|array',
            'id.*' => [
                'exists:appointments,id',
                function ($attribute, $value, $fail) {
                    $duplicates = Appointment::select('date', 'start_time')
                        ->whereIn('id', request('id'))
                        ->groupBy('date', 'start_time')
                        ->havingRaw('COUNT(*) > 1')
                        ->get();

                    if ($duplicates->isNotEmpty()) {
                        $fail('قرار ملاقات‌های تکراری مجاز نیستند.');
                    }
                },
            ],
        ];
    }
}
