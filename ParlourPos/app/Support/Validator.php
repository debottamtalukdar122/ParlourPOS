<?php

declare(strict_types=1);

namespace App\Support;

final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @param array<string, mixed> $data @param array<string, string> $rules */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleList) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleList) as $rule) {
                if ($rule === 'required' && ($value === null || trim((string) $value) === '')) {
                    $this->errors[$field] = "{$field} is required.";
                }
                if ($rule === 'email' && $value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "{$field} must be a valid email address.";
                }
            }
        }

        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array { return $this->errors; }
}
