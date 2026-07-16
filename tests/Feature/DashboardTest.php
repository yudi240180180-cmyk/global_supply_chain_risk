<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_home_page_shows_supply_chain_risk_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Global Supply Chain Risk Platform');
    }
}
