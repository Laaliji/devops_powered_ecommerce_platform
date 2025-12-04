@php
    $user = \Filament\Facades\Filament::auth()->user();
@endphp

<div class="w-full min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <style>
        /* Full width wizard container */
        .register-wizard-container {
            width: 100%;
            max-width: 100%;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Remove max-width constraints from Filament components */
        .fi-simple-main {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .fi-simple-page {
            max-width: none !important;
            width: 100% !important;
        }

        /* Wizard full width */
        .fi-fo-wizard {
            max-width: 100% !important;
            width: 100% !important;
            padding: 2rem 2rem !important;
        }

        .fi-fo-wizard-header {
            max-width: 100% !important;
            width: 100% !important;
        }

        .fi-fo-wizard-step-container {
            max-width: 100% !important;
            width: 100% !important;
        }

        /* Form full width */
        .fi-fo-form {
            max-width: 100% !important;
            width: 100% !important;
        }

        /* Remove constraints from all Filament form elements */
        [class*="fi-fo-"],
        [class*="fi-form"] {
            max-width: none !important;
        }

        /* Responsive padding for different screen sizes */
        @media (max-width: 640px) {
            .fi-fo-wizard {
                padding: 1rem 1rem !important;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .fi-fo-wizard {
                padding: 1.5rem 1.5rem !important;
            }
        }

        @media (min-width: 1025px) {
            .fi-fo-wizard {
                padding: 2rem 3rem !important;
            }
        }

        /* Submit button styling */
        button[type="submit"],
        .fi-btn-primary {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0.75rem 1.5rem !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        /* Hide loading spinners */
        .fi-loading-indicator,
        svg.animate-spin {
            display: none !important;
        }

        /* Wizard step buttons */
        .fi-fo-wizard-step-action {
            min-width: 120px !important;
        }

        /* Form actions container */
        .fi-form-actions,
        [class*="form-actions"] {
            width: 100% !important;
        }

        /* Page title styling */
        .register-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .register-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            font-size: 1rem;
            color: #6b7280;
        }

        /* Wrapper for centering */
        .register-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        /* Inner content wrapper */
        .register-inner {
            width: 100%;
            max-width: 1000px;
        }

        /* Login link styling */
        .register-login-link {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .register-login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .register-login-link a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>

    <div class="register-wrapper">
        <div class="register-inner">
            <!-- Header -->
            <div class="register-header">
                <h1>Create Your Account</h1>
                <p>Join us in just 4 simple steps</p>
            </div>

            <!-- Form Container -->
            <div class="register-wizard-container">
                <form id="form" wire:submit="register" class="w-full">
                    @csrf
                    
                    <!-- Wizard Form -->
                    {{ $this->form }}
                </form>
            </div>

            <!-- Login Link -->
            <div class="register-login-link">
                Already have an account?
                <a href="{{ route('filament.tenant.auth.login') }}">
                    Sign in here
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Listen for redirect event from Livewire - execute immediately
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized - redirect listener ready');
            
            Livewire.on('redirect-to-tenant', (event) => {
                console.log('Redirect event received:', event.url);
                
                // Immediate redirect - no delay
                window.location.href = event.url;
            });
        });
        
        // Also listen on document ready as backup
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Livewire) {
                console.log('DOMContentLoaded - setting up backup listener');
                window.Livewire.on('redirect-to-tenant', (event) => {
                    console.log('Backup redirect triggered:', event.url);
                    window.location.href = event.url;
                });
            }
        });
    </script>
</div>
