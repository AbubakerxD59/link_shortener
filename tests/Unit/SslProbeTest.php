<?php

namespace Tests\Unit;

use App\Services\SslProbe;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SslProbeTest extends TestCase
{
    #[DataProvider('statusCodeProvider')]
    public function test_response_status_code_parsing(?array $headers, ?int $expected): void
    {
        $probe = new class extends SslProbe
        {
            public function parse(?array $headers): ?int
            {
                return $this->responseStatusCode($headers);
            }
        };

        $this->assertSame($expected, $probe->parse($headers));
    }

    /**
     * @return array<string, array{0: ?list<string>, 1: ?int}>
     */
    public static function statusCodeProvider(): array
    {
        return [
            'http 200' => [['HTTP/1.1 200 OK'], 200],
            'http 525' => [['HTTP/1.1 525 <none>'], 525],
            'empty' => [null, null],
            'invalid' => [['Not-HTTP'], null],
        ];
    }
}
