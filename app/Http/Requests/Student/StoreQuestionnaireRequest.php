<?php

namespace App\Http\Requests\Student;

use App\Models\LearningStyleQuestion;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionnaireRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'responses' => ['required', 'array'],
            'responses.*.question_id' => ['required', 'integer', 'exists:learning_style_questions,id'],
            'responses.*.score' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'responses.required' => 'Jawaban kuesioner wajib diisi.',
            'responses.*.question_id.required' => 'ID pertanyaan wajib ada.',
            'responses.*.question_id.exists' => 'Pertanyaan tidak ditemukan.',
            'responses.*.score.required' => 'Skor jawaban wajib diisi.',
            'responses.*.score.min' => 'Skor minimal adalah 1.',
            'responses.*.score.max' => 'Skor maksimal adalah 5.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $activeQuestionCount = LearningStyleQuestion::active()->count();
            $responseCount = count($this->input('responses', []));

            if ($responseCount < $activeQuestionCount) {
                $validator->errors()->add('responses', 'Semua pertanyaan harus dijawab.');
            }
        });
    }
}
