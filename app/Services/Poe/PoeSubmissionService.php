<?php

namespace App\Services\Poe;

use App\Models\Campaign;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Valida y guarda un envío del formulario POE. Los campos válidos y las reglas se
 * derivan de la campaña; cualquier otro dato del request se ignora.
 */
class PoeSubmissionService
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function store(Campaign $campaign, int $customerId, array $input): int
    {
        $fields = $this->extractFields($input);
        [$rules, $labels] = $this->rules($campaign, $fields);

        $validator = Validator::make($fields, $rules, [
            'required' => 'Falta responder: :attribute.',
            'in' => 'Valor no válido en: :attribute.',
            'string' => 'Valor no válido en: :attribute.',
            'max' => 'Texto demasiado largo en: :attribute.',
        ], $labels);

        $validated = $validator->validate();

        $now = now();
        $rows = [];
        foreach ($validated as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $rows[] = [
                'customer_id' => $customerId,
                'meta_data_id' => (int) substr($key, 5),
                'audience_id' => config('poe.audience_id'),
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(fn () => CustomerMeta::insert($rows));

        return count($rows);
    }

    /**
     * Solo claves `meta_<id>` con id numérico; el alias `meta_34` (formularios abiertos
     * antes del deploy) equivale a `meta_4007` cuando trae un tipo de importación válido.
     *
     * @return array<string, mixed>
     */
    private function extractFields(array $input): array
    {
        $fields = [];
        foreach ($input as $key => $value) {
            if (is_string($key) && preg_match('/^meta_\d+$/', $key)) {
                $fields[$key] = $value;
            }
        }

        $importKey = 'meta_'.CustomerMetaData::POE_IMPORT_TYPE_ID;
        $legacyKey = 'meta_'.CustomerMetaData::POE_LEGACY_IMPORT_TYPE_ID;
        if (! isset($fields[$importKey]) && isset($fields[$legacyKey])
            && in_array($fields[$legacyKey], CustomerMetaData::POE_IMPORT_TYPES, true)) {
            $fields[$importKey] = $fields[$legacyKey];
        }

        return $fields;
    }

    /**
     * @return array{0: array<string, array>, 1: array<string, string>}
     */
    private function rules(Campaign $campaign, array $fields): array
    {
        $max = (int) config('poe.max_value_length');
        $importKey = 'meta_'.CustomerMetaData::POE_IMPORT_TYPE_ID;
        $isIndividual = ($fields[$importKey] ?? null) === CustomerMetaData::POE_INDIVIDUAL;

        $rules = [$importKey => ['required', 'string', Rule::in(CustomerMetaData::POE_IMPORT_TYPES)]];
        $labels = [$importKey => 'Importación de manera'];

        $campaign->loadMissing('customerMetaData.customerMetaDataChildren.customerMetaDataChildren');

        foreach ($campaign->customerMetaData as $group) {
            foreach ($group->customerMetaDataChildren as $child) {
                $key = 'meta_'.$child->id;
                $labels[$key] = $child->value;

                if ((int) $group->id === CustomerMetaData::POE_RESIDENCE_GROUP_ID) {
                    $options = $child->customerMetaDataChildren->pluck('value')->all();
                    $rules[$key] = $this->questionRules((int) $child->id, $options, $fields);

                    continue;
                }

                if ((int) $child->type_id === 5) {
                    continue; // sin subida de archivos en el POE
                }

                $required = ! ((int) $group->id === CustomerMetaData::POE_COMPANY_GROUP_ID && $isIndividual);
                $rules[$key] = [$required ? 'required' : 'nullable', 'string', 'max:'.$max];
            }
        }

        return [array_filter($rules), $labels];
    }

    /**
     * Residencia UE (siempre obligatoria) y EORI (solo si es residente).
     */
    private function questionRules(int $id, array $options, array $fields): array
    {
        if ($id === CustomerMetaData::POE_EU_RESIDENT_QUESTION_ID) {
            return ['required', 'string', Rule::in($options)];
        }

        if ($id === CustomerMetaData::POE_EORI_QUESTION_ID) {
            $isResident = ($fields['meta_'.CustomerMetaData::POE_EU_RESIDENT_QUESTION_ID] ?? null) === CustomerMetaData::POE_EU_RESIDENT_YES;

            return $isResident ? ['required', 'string', Rule::in($options)] : [];
        }

        return [];
    }
}
