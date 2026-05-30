<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'               => 'nullable|in:BORRADOR,REPORTADO,EN_REVISION,FINALIZADO',
            'title'                => 'nullable|string|max:200',
            'description'          => 'nullable|string|min:10|max:2000',
            'police_report_number' => 'nullable|string|max:60',
        ];
    }
}
