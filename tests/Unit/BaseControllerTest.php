<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BaseControllerTest extends TestCase
{
    public function test_base_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\Controller::class));
    }
}
