<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpsertDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'            => 'required|in:SOAT,TECNOMECANICA,LICENCIA',
            'document_number' => 'required|string|max:60',
            'issue_date'      => 'nullable|date',
            'expiry_date'     => 'required|date|after:issue_date',
            'notes'           => 'nullable|string|max:1000',
            'pdf_file'        => 'nullable|file|mimes:pdf|max:20480',
        ];
    }

    public function attributes(): array
    {
        return [
            'type'            => 'tipo de documento',
            'document_number' => 'número de documento',
            'issue_date'      => 'fecha de expedición',
            'expiry_date'     => 'fecha de vencimiento',
        ];
    }
}
