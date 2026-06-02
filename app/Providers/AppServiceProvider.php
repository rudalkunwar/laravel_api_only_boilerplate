<?php

declare(strict_types=1);

namespace App\Providers;

use App\User\Models\User;
use App\User\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureResources();
        $this->configurePasswordValidation();
        $this->configureRateLimiting();
        $this->configurePolicies();
        $this->configureNotifications();
    }

    private function configureModels(): void
    {
        $strict = !$this->app->isProduction();

        Model::preventSilentlyDiscardingAttributes($strict);
        Model::preventAccessingMissingAttributes($strict);
        Model::unguard(false);
    }

    private function configureResources(): void
    {
        JsonResource::withoutWrapping();
    }

    private function configurePasswordValidation(): void
    {
        Password::defaults(function (): Password {
            $rule = Password::min(8)->letters()->mixedCase()->numbers()->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            $identifier = $request->user()?->getAuthIdentifier();
            $key = is_scalar($identifier) ? (string) $identifier : ($request->ip() ?? 'guest');

            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('auth', static fn (Request $request): Limit => Limit::perMinute(5)->by((string) ($request->ip() ?? 'guest')));
    }

    private function configurePolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }

    private function configureNotifications(): void
    {
        ResetPassword::createUrlUsing(static function (mixed $notifiable, string $token): string {
            $frontendUrl = rtrim(Config::string('app.frontend_url'), '/');
            $email = $notifiable instanceof CanResetPassword
                ? urlencode($notifiable->getEmailForPasswordReset())
                : '';

            return sprintf('%s/reset-password?token=%s&email=%s', $frontendUrl, $token, $email);
        });
    }
}
