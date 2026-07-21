<?php

namespace App\Modules\SalesPerformanceReporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Extracted from RevenueForecastController::update()'s inline $request->validate()
// call. Rules are byte-for-byte identical to the original — structural move only.
class UpdateForecastAssumptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // No authorization/auth system in place yet — matches prior behavior.
        return true;
    }

    public function rules(): array
    {
        return [
            'growth_rate_pct'        => 'required|numeric|min:0|max:20',
            'deal_close_rate_pct'    => 'required|numeric|min:0|max:100',
            'seasonality_factor_pct' => 'required|numeric|min:0|max:100',
        ];
    }
}
