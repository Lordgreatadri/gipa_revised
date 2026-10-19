<?php

namespace App\Modules\Shared\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Emits the response security headers and a nonce-based Content Security
 * Policy configured in config/security.php.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // useCspNonce() without an argument overwrites any existing nonce.
        $nonce = Vite::cspNonce() ?: Vite::useCspNonce();

        $response = $next($request);

        foreach ((array) config('security.headers', []) as $header => $value) {
            $response->headers->set($header, $value);
        }

        if ($request->isSecure() && config('security.hsts.enabled')) {
            $hsts = 'max-age='.(int) config('security.hsts.max_age', 31536000);
            if (config('security.hsts.include_subdomains')) {
                $hsts .= '; includeSubDomains';
            }
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        if (config('security.csp.enabled')) {
            $headerName = config('security.csp.report_only')
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($headerName, $this->buildPolicy($nonce));
        }

        return $response;
    }

    private function buildPolicy(string $nonce): string
    {
        /** @var array<string, array<int, string>> $directives */
        $directives = (array) config('security.csp.directives', []);
        $nonceSource = "'nonce-{$nonce}'";

        foreach (['script-src', 'script-src-elem', 'style-src', 'style-src-elem'] as $directive) {
            $directives[$directive] = array_values(array_unique([...($directives[$directive] ?? ["'self'"]), $nonceSource]));
        }

        if ($devOrigin = $this->viteDevServerOrigin()) {
            foreach (['script-src', 'script-src-elem', 'style-src', 'style-src-elem', 'connect-src'] as $directive) {
                $directives[$directive][] = $devOrigin;
            }
            $directives['connect-src'][] = preg_replace('/^http/', 'ws', $devOrigin);
        }

        return collect($directives)
            ->map(fn (array $sources, string $name) => $name.' '.implode(' ', array_unique($sources)))
            ->implode('; ');
    }

    private function viteDevServerOrigin(): ?string
    {
        if (app()->isProduction() || ! Vite::isRunningHot()) {
            return null;
        }

        $url = rtrim((string) file_get_contents(public_path('hot')), "\n\r");
        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
