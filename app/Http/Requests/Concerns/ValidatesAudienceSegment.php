<?php

namespace App\Http\Requests\Concerns;

use App\Services\AudienceSegmentService;
use Illuminate\Validation\Validator;

trait ValidatesAudienceSegment
{
    protected function validateAudienceSegment(Validator $validator, bool $requireConditions = true): void
    {
        $segment = $this->segment();
        $conditions = $segment['conditions'] ?? null;

        if (! is_array($conditions)) {
            $validator->errors()->add('segment_json', 'El segmento debe incluir una lista de condiciones.');

            return;
        }

        if ($requireConditions && count($conditions) === 0) {
            $validator->errors()->add('segment_json', 'Debes agregar al menos una condición.');
        }

        $fieldConfig = AudienceSegmentService::getFieldConfig();

        foreach ($conditions as $index => $condition) {
            if (! is_array($condition)) {
                $validator->errors()->add("segment_json.conditions.$index", 'Cada condición debe tener formato de objeto.');

                continue;
            }

            $field = (string) ($condition['field'] ?? '');
            $operator = (string) ($condition['operator'] ?? '');
            $boolean = strtoupper((string) ($condition['boolean'] ?? 'AND'));
            $value = $condition['value'] ?? null;

            if (! array_key_exists($field, $fieldConfig)) {
                $validator->errors()->add("segment_json.conditions.$index.field", 'El campo seleccionado no está soportado.');

                continue;
            }

            if (! in_array($operator, $fieldConfig[$field]['operators'], true)) {
                $validator->errors()->add("segment_json.conditions.$index.operator", 'El operador no es válido para ese campo.');
            }

            if ($index > 0 && ! in_array($boolean, ['AND', 'OR'], true)) {
                $validator->errors()->add("segment_json.conditions.$index.boolean", 'El conector debe ser AND u OR.');
            }

            $this->validateConditionValue($validator, $index, $field, $operator, $value);
        }
    }

    protected function validateConditionValue(
        Validator $validator,
        int $index,
        string $field,
        string $operator,
        mixed $value
    ): void {
        if ($field === 'created_at') {
            $this->validateDateCondition($validator, $index, $operator, $value);

            return;
        }

        if (in_array($field, ['status_id', 'user_id', 'source_id', 'tag_id'], true)) {
            $this->validateIntegerCondition($validator, $index, $operator, $value);

            return;
        }

        if (in_array($field, ['name', 'business', 'city', 'country'], true)) {
            if (! is_string($value) || trim($value) === '') {
                $validator->errors()->add("segment_json.conditions.$index.value", 'Debes escribir un texto para esa condición.');
            }

            return;
        }

        if ($field === 'scoring_profile') {
            $this->validateScoringCondition($validator, $index, $operator, $value);

            return;
        }

        if ($field === 'has_whatsapp') {
            $booleanValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($booleanValue === null) {
                $validator->errors()->add("segment_json.conditions.$index.value", 'El valor debe ser verdadero o falso.');
            }
        }
    }

    protected function validateDateCondition(Validator $validator, int $index, string $operator, mixed $value): void
    {
        if ($operator === 'between') {
            if (! is_array($value) || count($value) !== 2) {
                $validator->errors()->add("segment_json.conditions.$index.value", 'Debes seleccionar una fecha inicial y una final.');

                return;
            }

            foreach ($value as $dateIndex => $dateValue) {
                if (! $this->isValidDate($dateValue)) {
                    $validator->errors()->add("segment_json.conditions.$index.value.$dateIndex", 'La fecha ingresada no es válida.');
                }
            }

            return;
        }

        if (! $this->isValidDate($value)) {
            $validator->errors()->add("segment_json.conditions.$index.value", 'La fecha ingresada no es válida.');
        }
    }

    protected function validateIntegerCondition(Validator $validator, int $index, string $operator, mixed $value): void
    {
        if ($operator === 'in') {
            if (! is_array($value) || $value === []) {
                $validator->errors()->add("segment_json.conditions.$index.value", 'Debes seleccionar al menos una opción.');

                return;
            }

            foreach ($value as $valueIndex => $item) {
                if (! $this->isPositiveInteger($item)) {
                    $validator->errors()->add("segment_json.conditions.$index.value.$valueIndex", 'Debes seleccionar valores numéricos válidos.');
                }
            }

            return;
        }

        if (! $this->isPositiveInteger($value)) {
            $validator->errors()->add("segment_json.conditions.$index.value", 'Debes seleccionar una opción válida.');
        }
    }

    protected function validateScoringCondition(Validator $validator, int $index, string $operator, mixed $value): void
    {
        $allowedValues = ['a', 'b', 'c', 'd'];

        if ($operator === 'in') {
            if (! is_array($value) || $value === []) {
                $validator->errors()->add("segment_json.conditions.$index.value", 'Debes seleccionar al menos un perfil.');

                return;
            }

            foreach ($value as $valueIndex => $item) {
                $profile = strtolower(trim((string) $item));
                if (! in_array($profile, $allowedValues, true)) {
                    $validator->errors()->add("segment_json.conditions.$index.value.$valueIndex", 'El perfil seleccionado no es válido.');
                }
            }

            return;
        }

        $profile = strtolower(trim((string) $value));
        if (! in_array($profile, $allowedValues, true)) {
            $validator->errors()->add("segment_json.conditions.$index.value", 'El perfil seleccionado no es válido.');
        }
    }

    protected function isPositiveInteger(mixed $value): bool
    {
        return is_numeric($value) && (int) $value > 0;
    }

    protected function isValidDate(mixed $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        return strtotime($value) !== false;
    }

    public function segment(): array
    {
        $decoded = json_decode((string) $this->input('segment_json', '{}'), true);

        return is_array($decoded) ? $decoded : [];
    }
}
