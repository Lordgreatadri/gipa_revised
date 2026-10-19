<?php

namespace App\Modules\Shared\Infrastructure\Api;

/**
 * Request samples for the public API reference. Each sample is the same
 * call, written for cURL, PHP, or Node.js.
 */
final class ApiCodeSamples
{
    /**
     * @param  array<string, mixed>  $operation
     * @return array{curl: string, php: string, node: string}
     */
    public function for(array $operation): array
    {
        $url = $this->url($operation);
        $method = strtoupper((string) $operation['method']);
        $bearer = ($operation['auth'] ?? 'none') === 'bearer';
        $payload = $this->payload($operation['request'] ?? null);

        return [
            'curl' => $this->curl($method, $url, $bearer, $payload),
            'php' => $this->php($method, $url, $bearer, $payload),
            'node' => $this->node($method, $url, $bearer, $payload),
        ];
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function url(array $operation): string
    {
        $path = (string) $operation['path'];

        foreach ($operation['parameters'] ?? [] as $parameter) {
            if (($parameter['in'] ?? '') !== 'path') {
                continue;
            }

            $path = str_replace('{'.$parameter['name'].'}', (string) ($parameter['example'] ?? '00000000-0000-4000-8000-000000000001'), $path);
        }

        $query = [];

        foreach ($operation['parameters'] ?? [] as $parameter) {
            if (($parameter['in'] ?? '') !== 'query' || ! array_key_exists('example', $parameter)) {
                continue;
            }

            $query[$parameter['name']] = $parameter['example'];
        }

        $url = rtrim((string) config('app.url'), '/').$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function payload(?string $request): ?array
    {
        if ($request === null || trim($request) === '') {
            return null;
        }

        $decoded = json_decode($request, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function curl(string $method, string $url, bool $bearer, ?array $payload): string
    {
        $lines = [
            'curl --request '.$method.' \\',
            "  --url '".$url."' \\",
            "  --header 'Accept: application/json' \\",
        ];

        if ($bearer) {
            $lines[] = "  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \\";
        }

        if ($payload !== null) {
            $lines[] = "  --header 'Content-Type: application/json' \\";
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $lines[] = "  --data '".str_replace("'", "'\\''", (string) $json)."'";
        } else {
            $lines[array_key_last($lines)] = rtrim((string) $lines[array_key_last($lines)], ' \\');
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function php(string $method, string $url, bool $bearer, ?array $payload): string
    {
        $headers = ["'Accept: application/json'"];

        if ($bearer) {
            $headers[] = "'Authorization: Bearer '.\$accessToken";
        }

        if ($payload !== null) {
            $headers[] = "'Content-Type: application/json'";
        }

        $headerBlock = implode(",\n        ", $headers);
        $tokenLine = $bearer ? "\$accessToken = 'YOUR_ACCESS_TOKEN';\n" : '';
        $body = $payload === null ? '' : "\n\$payload = ".$this->phpValue($payload, 1).";\n";
        $post = $payload === null ? '' : "\n    CURLOPT_POSTFIELDS => json_encode(\$payload, JSON_THROW_ON_ERROR),";

        return <<<PHP
<?php

{$tokenLine}{$body}
\$ch = curl_init('{$url}');
curl_setopt_array(\$ch, [
    CURLOPT_CUSTOMREQUEST => '{$method}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        {$headerBlock},
    ],{$post}
]);

\$raw = curl_exec(\$ch);
\$status = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

\$body = json_decode(\$raw, true);
PHP;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function node(string $method, string $url, bool $bearer, ?array $payload): string
    {
        $headers = ["    Accept: 'application/json',"];

        if ($bearer) {
            $headers[] = "    Authorization: `Bearer \${accessToken}`,";
        }

        if ($payload !== null) {
            $headers[] = "    'Content-Type': 'application/json',";
        }

        $headerBlock = implode("\n", $headers);
        $tokenLine = $bearer ? "const accessToken = 'YOUR_ACCESS_TOKEN';\n" : '';
        $payloadBlock = $payload === null
            ? ''
            : "\nconst payload = ".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).";\n";
        $bodyLine = $payload === null ? '' : "\n  body: JSON.stringify(payload),";

        return <<<JS
{$tokenLine}{$payloadBlock}
const response = await fetch('{$url}', {
  method: '{$method}',
  headers: {
{$headerBlock}
  },{$bodyLine}
});

const body = await response.json();
console.log(response.status, body);
JS;
    }

    private function phpValue(mixed $value, int $level): string
    {
        if (! is_array($value)) {
            return match (true) {
                is_string($value) => var_export($value, true),
                is_bool($value) => $value ? 'true' : 'false',
                $value === null => 'null',
                default => (string) $value,
            };
        }

        if ($value === []) {
            return '[]';
        }

        $pad = str_repeat('    ', $level);
        $end = str_repeat('    ', $level - 1);
        $lines = [];
        $list = array_is_list($value);

        foreach ($value as $key => $item) {
            $rendered = $this->phpValue($item, $level + 1);
            $lines[] = $list
                ? $pad.$rendered.','
                : $pad.var_export((string) $key, true).' => '.$rendered.',';
        }

        return "[\n".implode("\n", $lines)."\n".$end.']';
    }
}
