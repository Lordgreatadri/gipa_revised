<?php

namespace Tests\Unit\Modules\Shared;

use App\Modules\Shared\Infrastructure\Sms\Providers\FrogEndpoint;
use PHPUnit\Framework\TestCase;

class FrogEndpointTest extends TestCase
{
    public function test_a_host_and_the_documented_prefix_both_call_the_send_path_once(): void
    {
        $expected = 'https://frogapi.wigal.com.gh/api/v3/sms/send';

        $this->assertSame($expected, FrogEndpoint::url('https://frogapi.wigal.com.gh', 'sms/send'));
        $this->assertSame($expected, FrogEndpoint::url('https://frogapi.wigal.com.gh/api/v3', 'sms/send'));
        $this->assertSame($expected, FrogEndpoint::url('https://frogapi.wigal.com.gh/api/v3/sms/send', 'sms/send'));
        $this->assertSame(
            'https://frogapi.wigal.com.gh/api/v3/balance',
            FrogEndpoint::url('https://frogapi.wigal.com.gh/api/v3/balance', 'balance'),
        );
    }
}
