<?php

namespace Tests\Feature;

use App\Models\CustomDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBrandedDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function makePendingDomain(User $owner, array $overrides = []): CustomDomain
    {
        return CustomDomain::create(array_merge([
            'user_id' => $owner->id,
            'domain' => 'go.brand.test',
            'domain_type' => CustomDomain::TYPE_SUBDOMAIN,
            'base_domain' => 'brand.test',
            'subdomain_prefix' => 'go',
            'verification_token' => 'token123',
            'verified_at' => null,
        ], $overrides));
    }

    public function test_guest_cannot_view_admin_branded_domains(): void
    {
        $this->get(route('admin.branded-domains.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_admin_branded_domains(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.branded-domains.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_pending_branded_domains(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create(['name' => 'Domain Owner', 'email' => 'owner@example.com']);
        $this->makePendingDomain($owner);

        $this->actingAs($admin)
            ->get(route('admin.branded-domains.index'))
            ->assertOk()
            ->assertSee('Pending branded domains')
            ->assertSee('How to activate a domain on Hostinger')
            ->assertSee('Parked Domains')
            ->assertSee('go.brand.test')
            ->assertSee('owner@example.com')
            ->assertSee('Mark active');
    }

    public function test_admin_panel_hides_already_active_domains(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $this->makePendingDomain($owner, [
            'domain' => 'active.brand.test',
            'verified_at' => now(),
        ]);
        $this->makePendingDomain($owner, [
            'domain' => 'pending.brand.test',
            'subdomain_prefix' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.branded-domains.index'))
            ->assertOk()
            ->assertSee('pending.brand.test')
            ->assertDontSee('active.brand.test');
    }

    public function test_admin_can_activate_pending_domain(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $domain = $this->makePendingDomain($owner);

        $this->actingAs($admin)
            ->post(route('admin.branded-domains.activate', $domain))
            ->assertRedirect(route('admin.branded-domains.index'))
            ->assertSessionHas('domain_status');

        $this->assertNotNull($domain->fresh()->verified_at);
    }

    public function test_non_admin_cannot_activate_domain(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $domain = $this->makePendingDomain($owner);

        $this->actingAs($user)
            ->post(route('admin.branded-domains.activate', $domain))
            ->assertForbidden();

        $this->assertNull($domain->fresh()->verified_at);
    }

    public function test_adding_branded_domain_shows_review_toast_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('branded-domains.store'), [
                'base_domain' => 'brand.test',
                'domain_type' => 'subdomain',
                'subdomain_prefix' => 'go',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('toast.message', 'Domain submitted. It will take up to 24 hours for our team to review and activate it.')
            ->assertSessionHas('domain_status');
    }
}
