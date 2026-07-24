<?php

namespace App\Modules\SalesPerformanceReporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Shared by both store() and update() on AlertsController — the validation
// rules are identical for create and edit, so one FormRequest covers both
// rather than duplicating the same array across StoreAlertRequest and
// UpdateAlertRequest. Extracted from the controller's private validated()
// helper with the rules left byte-for-byte unchanged (structural move only).
class AlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No authorization/auth system in place yet — matches prior
        // behavior, where the controller performed no authorization check.
        return true;
    }

    public function rules(): array
    {
        return [
            'category'    => 'required|in:critical,warning,positive,info',
            'title'       => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'link_label'  => 'nullable|string|max:100',
            'link_url'    => 'nullable|string|max:255',
        ];
    }
}
