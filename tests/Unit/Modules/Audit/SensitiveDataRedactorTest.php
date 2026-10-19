<?php

namespace Tests\Unit\Modules\Audit;

use App\Modules\Audit\Application\SensitiveDataRedactor;
use PHPUnit\Framework\TestCase;

class SensitiveDataRedactorTest extends TestCase
{
    public function test_it_redacts_configured_keys_recursively_and_case_insensitively(): void
    {
        $redactor = new SensitiveDataRedactor(['password', 'code', 'token'], '[REDACTED]');

        $result = $redactor->redact([
            'name' => 'Ama',
            'Password' => 'secret',
            'nested' => [
                'CODE' => '123456',
                'safe' => 'kept',
                'deeper' => ['token' => 'abc', 'list' => [1, 2]],
            ],
        ]);

        $this->assertSame([
            'name' => 'Ama',
            'Password' => '[REDACTED]',
            'nested' => [
                'CODE' => '[REDACTED]',
                'safe' => 'kept',
                'deeper' => ['token' => '[REDACTED]', 'list' => [1, 2]],
            ],
        ], $result);
    }
}
