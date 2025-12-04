<?php

namespace App\Filament\Pages\Auth;

use App\Models\Tenant;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as AuthRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Tenant public registration page - accessible without authentication or tenant context.
 * Automatically creates tenant and assigns roles on registration.
 * Follows Oura pattern: Auth pages bypass all Shield authorization.
 */
class Register extends AuthRegister
{
    use WithRateLimiting;

    public ?Tenant $createdTenant = null;
    public ?string $generatedSlug = null;

    /**
     * Auth pages are always accessible - no permissions required.
     * This is critical to prevent 403 Forbidden errors.
     */
    public static function canAccess(): bool
    {
        return true;
    }

    /**
     * Don't show in navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        if (app()->isLocal()) {
            $this->form->fill([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'mobile' => '+1234567890',
                'dob' => '1990-01-01',
                'sex' => 'male',
                'organization_name' => 'Acme Corporation',
                'subdomain' => 'acme-corp',
                'password' => 'Password123!@#',
                'password_confirmation' => 'Password123!@#',
            ]);
        } else {
            $this->form->fill();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Personal Information
                    Wizard\Step::make('Personal Information')
                        ->icon('heroicon-m-user')
                        ->completedIcon('heroicon-m-check')
                        ->description('Tell us about yourself')
                        ->columns(1)
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            $this->getMobileFormComponent(),
                        ]),

                    // Step 2: Additional Details
                    Wizard\Step::make('Additional Details')
                        ->icon('heroicon-m-calendar')
                        ->completedIcon('heroicon-m-check')
                        ->description('Complete your profile')
                        ->columns(2)
                        ->schema([
                            $this->getDobFormComponent(),
                            $this->getSexFormComponent(),
                        ]),

                    // Step 3: Organization Setup
                    Wizard\Step::make('Organization Setup')
                        ->icon('heroicon-m-building-office-2')
                        ->completedIcon('heroicon-m-check')
                        ->description('Create your organization')
                        ->columns(1)
                        ->schema([
                            $this->getOrganizationNameFormComponent(),
                            $this->getSubdomainFormComponent(),
                        ]),

                    // Step 4: Security
                    Wizard\Step::make('Security')
                        ->icon('heroicon-m-lock-closed')
                        ->completedIcon('heroicon-m-check')
                        ->description('Set your password')
                        ->columns(1)
                        ->schema([
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                ])
                ->skippable(false)
                ->persistStepInQueryString()
                ->submitAction(new \Illuminate\Support\HtmlString(
                    \Illuminate\Support\Facades\Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="lg"
                            class="w-full"
                        >
                            Create Account
                        </x-filament::button>
                    BLADE)
                )),
            ])
            ->statePath('data');
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Full Name')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email Address')
            ->email()
            ->required()
            ->maxLength(255)
            ->autocomplete('email')
            ->unique(User::class);
    }

    protected function getMobileFormComponent(): Component
    {
        return TextInput::make('mobile')
            ->label('Mobile Number')
            ->tel()
            ->required()
            ->unique(User::class)
            ->helperText('We\'ll use this to contact you');
    }

    protected function getDobFormComponent(): Component
    {
        return DatePicker::make('dob')
            ->label('Date of Birth')
            ->required()
            ->maxDate(now()->subYears(15)) // Minimum age 15
            ->minDate(now()->subYears(100)) // Maximum age 100
            ->displayFormat('d/m/Y')
            ->helperText('You must be at least 15 years old');
    }

    protected function getSexFormComponent(): Component
    {
        return Select::make('sex')
            ->label('Gender')
            ->required()
            ->options([
                'male' => 'Male',
                'female' => 'Female',
            ])
            ->native(false);
    }

    protected function getOrganizationNameFormComponent(): Component
    {
        return TextInput::make('organization_name')
            ->label('Organization Name')
            ->required()
            ->minLength(2)
            ->maxLength(50)
            ->rules([
                'regex:/^[\p{L}\d\s\-\.]+$/u',
            ])
            ->helperText('Your company or business name')
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, callable $set) {
                if ($state) {
                    $slug = str($state)->slug()->toString();
                    $this->generatedSlug = $this->generateUniqueSlug($slug);
                    $set('subdomain', $this->generatedSlug);
                }
            });
    }

    protected function getSubdomainFormComponent(): Component
    {
        return TextInput::make('subdomain')
            ->label('Subdomain')
            ->required()
            ->minLength(3)
            ->maxLength(30)
            ->rules([
                'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
                'not_in:admin,api,www,mail,ftp,localhost,tenant,app,dashboard',
                'unique:tenants,slug',
            ])
            ->prefix('https://')
            ->suffix('.' . config('app.domain', 'localhost'))
            ->helperText('Your unique organization subdomain (e.g., my-company)')
            ->live(onBlur: true);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->required()
            ->rules([
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3),
            ])
            ->confirmed()
            ->revealable()
            ->maxLength(255)
            ->autocomplete('new-password')
            ->helperText('Minimum 12 characters with uppercase, lowercase, numbers and symbols');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label('Confirm Password')
            ->password()
            ->required()
            ->revealable()
            ->maxLength(255)
            ->autocomplete('new-password');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Create Account';
    }

    public function getHeading(): string | Htmlable
    {
        return new HtmlString(
            '<a href="/" class="flex flex-col items-center gap-4">' .
                'Register Your Organization' .
                '<span class="text-sm text-gray-500">Back to home</span>' .
                '</a>'
        );
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Registration throttled')
                ->body("Please wait {$exception->secondsUntilAvailable} seconds before trying again")
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        // Create or find tenant
        $tenant = $this->createOrFindTenant($data);

        // Store tenant for use in handleRegistration
        $this->createdTenant = $tenant;

        $user = $this->handleRegistration($data);

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();
        
        // Show success notification
        Notification::make()
            ->title('Registration successful!')
            ->body("Redirecting to {$tenant->name}...")
            ->success()
            ->send();
        
        // Build tenant URL
        $protocol = request()->secure() ? 'https' : 'http';
        $domain = config('app.domain', 'localhost');
        $port = '';
        
        if (request()->getPort() && 
            !(request()->secure() && request()->getPort() === 443) && 
            !(!request()->secure() && request()->getPort() === 80)) {
            $port = ':' . request()->getPort();
        }
        
        $url = "{$protocol}://{$tenant->slug}.{$domain}{$port}/tenant";
        
        // Dispatch browser event to redirect (JavaScript will handle the actual redirect)
        $this->dispatch('redirect-to-tenant', url: $url);
        
        // Return the response (but JavaScript redirect will take precedence)
        return app(TenantRegistrationResponse::class);
    }

    protected function createOrFindTenant(array $data): Tenant
    {
        try {
            // Use the subdomain from form data
            $slug = $data['subdomain'];

            return Tenant::create([
                'name' => $data['organization_name'],
                'slug' => $slug,
                'type' => 'ecommerce',
            ]);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to create organization')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function generateUniqueSlug(string $baseSlug): string
    {
        $reservedSlugs = ['admin', 'api', 'www', 'mail', 'ftp', 'localhost', 'tenant', 'app', 'dashboard'];

        // Check if base slug is reserved
        if (in_array($baseSlug, $reservedSlugs)) {
            $baseSlug = $baseSlug . '-org';
        }

        $slug = $baseSlug;
        $counter = 1;

        // Keep trying until we find a unique slug
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function handleRegistration(array $data): Model
    {
        // Hash password
        $data['password'] = Hash::make($data['password']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'dob' => $data['dob'],
            'sex' => $data['sex'],
            'password' => $data['password'],
        ]);

        // Assign tenant role (since they created the organization)
        $user->assignRole('tenant');

        // Attach to created tenant as owner
        $user->tenants()->attach($this->createdTenant->id);

        // Set as current tenant
        $user->update(['current_tenant_id' => $this->createdTenant->id]);

        return $user;
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Registration failed. Please check your information and try again.',
        ]);
    }

    public function getView(): string
    {
        return 'filament.pages.auth.register';
    }

    public function getViewData(): array
    {
        return [];
    }
}
