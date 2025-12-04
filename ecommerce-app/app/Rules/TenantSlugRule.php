<?php

namespace App\Rules;

use App\Models\Tenant;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TenantSlugRule implements ValidationRule
{
    /**
     * The ID to ignore during uniqueness check (for updates).
     *
     * @var int|null
     */
    protected ?int $ignoreId = null;

    /**
     * Create a new rule instance.
     *
     * @param int|null $ignoreId
     */
    public function __construct(?int $ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convert to lowercase for validation
        $value = strtolower($value);

        // Check length: 3-30 characters
        $minLength = config('tenants.slug.min_length', 3);
        $maxLength = config('tenants.slug.max_length', 30);

        if (strlen($value) < $minLength || strlen($value) > $maxLength) {
            $fail("The {$attribute} must be between {$minLength} and {$maxLength} characters.");
            return;
        }

        // Format validation: lowercase, numbers, hyphens only
        // Must start and end with alphanumeric, hyphens only in middle
        $pattern = config('tenants.slug.pattern', '/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/');
        
        if (!preg_match($pattern, $value)) {
            $fail("The {$attribute} can only contain lowercase letters, numbers, and hyphens. It must start and end with a letter or number.");
            return;
        }

        // Check for consecutive hyphens
        if (str_contains($value, '--')) {
            $fail("The {$attribute} cannot contain consecutive hyphens.");
            return;
        }

        // Check reserved slugs
        $reservedSlugs = config('tenants.reserved_slugs', [
            'admin', 'api', 'www', 'mail', 'ftp', 'localhost', 'tenant', 'app',
            'dashboard', 'support', 'help', 'docs', 'blog', 'shop', 'store',
            'cdn', 'static', 'assets', 'public', 'private', 'system', 'root',
            'test', 'staging', 'dev', 'demo',
        ]);

        if (in_array($value, $reservedSlugs, true)) {
            $fail("The {$attribute} '{$value}' is reserved and cannot be used.");
            return;
        }

        // Check uniqueness in database
        $query = Tenant::where('slug', $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} '{$value}' is already taken.");
            return;
        }
    }

    /**
     * Set the ID to ignore during uniqueness check.
     *
     * @param int $id
     * @return $this
     */
    public function ignore(int $id): self
    {
        $this->ignoreId = $id;
        return $this;
    }
}
