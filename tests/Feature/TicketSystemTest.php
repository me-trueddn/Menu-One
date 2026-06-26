<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Tenant;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\TicketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user', 'platform_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->seed(TicketSeeder::class);
        $this->seed(\Database\Seeders\ModulePermissionSeeder::class);
    }

    private function createLinkedTenant(User $customer): Tenant
    {
        $tenant = Tenant::create([
            'id' => 'ticket-cafe-1',
            'name' => 'Ticket Cafe',
            'slug' => 'ticket-cafe',
            'is_active' => true,
            'owner_user_id' => $customer->id,
        ]);

        $customer->update(['tenant_id' => $tenant->id]);
        $customer->assignedTenants()->attach($tenant->id);

        return $tenant;
    }

    public function test_customer_can_open_ticket_as_new_and_reply_sets_answered(): void
    {
        $customer = User::factory()->create(['tenant_id' => null]);
        $customer->assignRole('user');
        $tenant = $this->createLinkedTenant($customer);

        $category = TicketCategory::query()->first();

        $response = $this->actingAs($customer)->post(route('profile.tickets.store'), [
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'subject' => 'Ödeme sorunu',
            'body' => '<p>Merhaba, <strong>yardım</strong> lazım.</p>',
            'body_format' => 'html',
        ]);

        $response->assertRedirect(route('profile.edit', [
            'tab' => 'ticket',
            'ticket_id' => $customer->fresh()->tickets()->first()->id,
        ]));

        $ticket = $customer->fresh()->tickets()->first();
        $this->assertNotNull($ticket);
        $this->assertSame($tenant->id, $ticket->tenant_id);
        $this->assertSame(TicketStatus::New, $ticket->status);

        $this->actingAs($customer)->post(route('profile.tickets.reply', $ticket), [
            'body' => '<p>Ek bilgi: hata devam ediyor.</p>',
        ])->assertRedirect(route('profile.edit', ['tab' => 'ticket', 'ticket_id' => $ticket->id]));

        $this->assertSame(TicketStatus::Answered, $ticket->fresh()->status);
    }

    public function test_platform_staff_can_reply_and_assign_ticket(): void
    {
        $customer = User::factory()->create(['tenant_id' => null]);
        $customer->assignRole('user');
        $tenant = $this->createLinkedTenant($customer);

        $staff = User::factory()->create(['tenant_id' => null]);
        $staff->assignRole('platform_admin');

        $category = TicketCategory::query()->first();

        $ticket = app(TicketService::class)->createForCustomer(
            $customer,
            $tenant,
            $category,
            'API hatası',
            '<p>Webhook çalışmıyor</p>',
        );

        $response = $this->actingAs($staff)
            ->post(route('platform.tickets.reply', $ticket), ['body' => '<p>İnceliyoruz.</p>']);

        $response->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame($staff->id, $ticket->assigned_to_user_id);
        $this->assertSame(TicketStatus::InProgress, $ticket->status);
    }
}
