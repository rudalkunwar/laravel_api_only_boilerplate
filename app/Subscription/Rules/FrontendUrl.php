<?php

declare(strict_types=1);

namespace App\Subscription\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;

final class FrontendUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || $this->origin($value) !== $this->origin(Config::string('app.frontend_url'))) {
            $fail('The :attribute must use the configured frontend origin.');
        }
    }

    private function origin(string $url): ?string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (!is_string($scheme) || !is_string($host)) {
            return null;
        }

        $scheme = mb_strtolower($scheme);
        $port = is_int($port) && (($scheme !== 'http' || $port !== 80) && ($scheme !== 'https' || $port !== 443))
            ? ':'.$port
            : '';

        return $scheme.'://'.mb_strtolower($host).$port;
    }
}
