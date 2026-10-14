<?php

namespace Tests\Feature;

use App\Http\Controllers\StockDetailsCheckController;
use App\Models\User;
use Tests\TestCase;

class StockCheckReportAccessTest extends TestCase
{
    private function user(string $role): User
    {
        return (new User())->forceFill([
            'id' => 1,
            'username' => 'stock-access-test',
            'role' => $role,
            'BC' => '001',
        ]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/StockCheckreport')->assertRedirect(route('login'));
        $this->post('/update-checkbox', ['record_id' => 1, 'checked' => true])
            ->assertRedirect(route('login'));
        $this->post('/reset-checkbox-stock')->assertRedirect(route('login'));
    }

    public function test_non_admin_roles_cannot_view_or_change_stock_check_report(): void
    {
        foreach (['Cashier', 'Manager', 'User'] as $role) {
            $this->actingAs($this->user($role));
            $this->get('/StockCheckreport')->assertForbidden();
            $this->post('/update-checkbox', ['record_id' => 1, 'checked' => true])
                ->assertForbidden();
            $this->post('/reset-checkbox-stock')->assertForbidden();
        }
    }

    public function test_admin_can_reach_the_report_and_its_actions(): void
    {
        $this->actingAs($this->user('Admin'));
        $controller = $this->partialMock(StockDetailsCheckController::class);
        $controller->shouldReceive('StockCheck')->once()->andReturn(response('report'));
        $controller->shouldReceive('updateCheckbox')->once()->andReturn(response('updated'));
        $controller->shouldReceive('resetCheckboxStock')->once()->andReturn(response('reset'));

        $this->get('/StockCheckreport')->assertOk()->assertSee('report');
        $this->post('/update-checkbox', ['record_id' => 1, 'checked' => true])
            ->assertOk()->assertSee('updated');
        $this->post('/reset-checkbox-stock')->assertOk()->assertSee('reset');
    }
}
