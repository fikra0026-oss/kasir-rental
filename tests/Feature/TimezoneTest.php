<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function test_runtime_timezone_matches_application_timezone(): void
    {
        $this->assertSame(config('app.timezone'), date_default_timezone_get());
    }
}
