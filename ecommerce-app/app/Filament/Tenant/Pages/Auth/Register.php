<?php

namespace App\Filament\Tenant\Pages\Auth;

use App\Models\Tenant;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class Register extends BaseRegister
{
    protected ?Tenant $createdTenant = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                
                // Organization Name (generates slug)
                TextInput::make('organization_name')
                    ->label('Organization Name')
                    ->required()
                    ->minLength(2)
                    ->maxLength(50)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        if ($state) {
                            $slug = Str::slug($state);
                            $uniqueSlug = $this->generateUniqueSlug($slug);
                            $set('subdomain', $uniqueSlug);
                        }
                    }),
                    
                // Subdomain (Slug)
                TextInput::make('subdomain')
                    ->label('Subdomain')
                    ->required()
                    ->minLength(3)
                    ->maxLength(30)
                    ->prefix('https://')
                    ->suffix('.' . config('app.domain', 'localhost'))
                    ->rules([
                        'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
                        'not_in:' . implode(',', $this->getReservedSlugs()),
                        'unique:tenants,slug',
                    ])
                    ->validationMessages([
                        'regex' => 'The subdomain must only contain lowercase letters, numbers, and hyphens.',
                        'not_in' => 'This subdomain is reserved and cannot be used.',
                        'unique' => 'This subdomain is already taken.',
                        'min' => 'The subdomain must be at least 3 characters.',
                        'max' => 'The subdomain must not exceed 30 characters.',
                    ])
                    ->helperText('Choose a unique subdomain for your organization (e.g., "my-company")')
                    ->live(onBlur: true),
            ]);
    }

    /**
     * Get reserved slugs from config.
     */
    protected function getReservedSlugs(): array
    {
        return config('tenants.reserved_slugs', [
            'admin', 'api', 'www', 'mail', 'ftp', 'localhost', 
            'tenant', 'app', 'dashboard', 'panel', 'auth', 'account',
        ]);
    }

    /**
     * Generate a unique slug from a base slug.
     * 
     * Handles:
     * 1. Reserved slug detection (e.g., 'admin' -> 'admin-org')
     * 2. Uniqueness enforcement (e.g., 'tech-solutions' -> 'tech-solutions-1')
     */
    protected function generateUniqueSlug(string $baseSlug): string
    {
        $reservedSlugs = $this->getReservedSlugs();

        // Step 1: Check if reserved
        if (in_array($baseSlug, $reservedSlugs)) {
            $baseSlug = $baseSlug . '-org';
        }

        // Step 2: Ensure uniqueness
        $slug = $baseSlug;
        $counter = 1;

        // Keep trying until we find a unique slug
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle the registration process.
     * 
     * Creates:
     * 1. Tenant record (with generated slug)
     * 2. User record (with current_tenant_id)
     * 3. User-Tenant association (many-to-many)
     * 4. Role assignment (tenant)
     */
    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Step 1: Create User
            $user = $this->getUserModel()::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            // Step 2: Create Tenant with auto-generated slug
            $tenant = Tenant::create([
                'name' => $data['organization_name'],
                'slug' => $data['subdomain'],  // Auto-generated and validated
                'type' => 'ecommerce',
                'user_id' => $user->id,
                'primary_color' => '#3B82F6',  // Default Filament blue
            ]);

            // Store for later use in redirect
            $this->createdTenant = $tenant;

            // Step 3: Assign Role
            $user->assignRole('tenant');

            // Step 4: Attach user to tenant (many-to-many relationship)
            $tenant->users()->attach($user->id);
            
            // Step 5: Set current tenant on user
            $user->update(['current_tenant_id' => $tenant->id]);

            return $user;
        });
    }

    /**
     * Get the response to the completed registration attempt.
     * 
     * Logs out any existing session, then redirects to the new tenant's subdomain.
     * This ensures the user is fully logged out from previous tenant contexts
     * and can properly authenticate on the new subdomain.
     */
    protected function getRegistrationResponse(): RegistrationResponse
    {
        $tenant = $this->createdTenant ?? $this->getUser()->currentTenant;
        
        if (!$tenant) {
            throw new \RuntimeException('Unable to determine tenant for registration redirect.');
        }

        return new class($tenant) implements RegistrationResponse {
            protected Tenant $tenant;
            
            public function __construct(Tenant $tenant)
            {
                $this->tenant = $tenant;
            }
            
            public function toResponse($request): RedirectResponse
            {
                // Log out any existing authenticated user to clear old tenant session
                Auth::logout();
                
                // Invalidate the session to ensure clean state
                session()->invalidate();
                
                // Regenerate session token for security
                session()->regenerateToken();
                
                // Extract protocol from current request
                $protocol = $request->secure() ? 'https' : 'http';
                $domain = config('app.domain', 'localhost');
                
                // Extract port if it exists in the current URL
                $port = '';
                if ($request->getPort() && 
                    !($request->secure() && $request->getPort() === 443) && 
                    !(!$request->secure() && $request->getPort() === 80)) {
                    $port = ':' . $request->getPort();
                }
                
                // Build the tenant subdomain URL - redirect to login page on new subdomain
                // User will need to log in again with their credentials on the new subdomain
                $url = "{$protocol}://{$this->tenant->slug}.{$domain}{$port}/tenant/login";
                
                // Redirect to login page on the new subdomain
                return redirect()->away($url);
            }
        };
    }

    public function getRegisterFormAction(): \Filament\Actions\Action
    {
        return parent::getRegisterFormAction()
            ->submit('register');
    }
}


