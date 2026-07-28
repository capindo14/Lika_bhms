<?php

namespace App\Services;

class ValidationService {
    private $errors = [];

    /**
     * Validate rules for inputs
     * 
     * @param array $data Input dataset
     * @param array $rules Rules key-value maps e.g. ['fullname' => 'required|min:3', 'birthdate' => 'required|date']
     * @return bool
     */
    public function validate(array $data, array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $fieldRules);

            foreach ($ruleList as $rule) {
                // Rule arguments format check (e.g. min:3)
                $arg = null;
                if (strpos($rule, ':') !== false) {
                    list($rule, $arg) = explode(':', $rule);
                }

                switch ($rule) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " field is required.");
                        }
                        break;
                    case 'min':
                        if (strlen((string)$value) < (int)$arg) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be at least {$arg} characters.");
                        }
                        break;
                    case 'max':
                        if (strlen((string)$value) > (int)$arg) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must not exceed {$arg} characters.");
                        }
                        break;
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a valid number.");
                        }
                        break;
                    case 'date':
                        if (!empty($value) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a valid date (YYYY-MM-DD).");
                        }
                        break;
                    case 'phone':
                        if (!empty($value) && !preg_match('/^(09|\+639)\d{9}$/', $value)) {
                            $this->addError($field, "The " . str_replace('_', ' ', $field) . " must be a valid Philippine mobile number (e.g. 09171234567).");
                        }
                        break;
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Log a validation error
     * 
     * @param string $field
     * @param string $message
     */
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    /**
     * Retrieve list of errors
     * 
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function getFirstError(): ?string {
        if (empty($this->errors)) return null;
        return reset($this->errors);
    }
}
