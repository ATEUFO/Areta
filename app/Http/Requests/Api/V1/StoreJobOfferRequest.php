<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isParent() ?? false;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'subject'      => ['required', 'string', 'max:100'],
            'objective'    => ['nullable', 'string', 'max:2000'],
            'frequency'    => ['required', 'in:1x_semaine,2x_semaine,3x_semaine,quotidien,weekend,a_definir'],
            'school_level' => ['nullable', 'in:maternelle,primaire,collège,lycée,supérieur'],
            'city'         => ['required', 'string', 'max:100'],
            'district'     => ['nullable', 'string', 'max:100'],
            'lat'          => ['nullable', 'numeric', 'between:-90,90'],
            'lng'          => ['nullable', 'numeric', 'between:-180,180'],
            'budget_min'   => ['nullable', 'numeric', 'min:0'],
            'budget_max'   => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'child_id'     => ['nullable', 'exists:children,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'     => 'Le titre de l\'offre est requis.',
            'subject.required'   => 'La matière est requise.',
            'frequency.required' => 'La fréquence est requise.',
            'frequency.in'       => 'Fréquence invalide.',
            'city.required'      => 'La ville est requise.',
            'budget_max.gte'     => 'Le budget maximum doit être supérieur au minimum.',
        ];
    }
}
