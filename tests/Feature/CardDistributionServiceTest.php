<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\RechargeCard;
use App\Services\CardDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardDistributionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $distributor;
    protected User $customer;
    protected CardDistributionService $cardService;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        
        $adminRole = Role::factory()->create(['name' => 'admin', 'level' => 90]);
        $distributorRole = Role::factory()->create(['name' => 'card-distributor', 'level' => 40]);
        $customerRole = Role::factory()->create(['name' => 'customer', 'level' => 10]);

        $this->admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->admin->roles()->attach($adminRole);

        $this->distributor = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->distributor->roles()->attach($distributorRole);

        $this->customer = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->customer->roles()->attach($customerRole);

        $this->cardService = app(CardDistributionService::class);
    }

    public function test_can_generate_cards(): void
    {
        $cards = $this->cardService->generateCards(5, 100.00, $this->admin);

        $this->assertCount(5, $cards);
        
        foreach ($cards as $card) {
            $this->assertInstanceOf(RechargeCard::class, $card);
            $this->assertEquals(100.00, $card->denomination);
            $this->assertEquals('active', $card->status);
            $this->assertEquals($this->admin->id, $card->generated_by);
            $this->assertMatchesRegularExpression('/^RC-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $card->card_number);
            $this->assertIsString($card->pin);
            $this->assertEquals(4, strlen($card->pin));
        }
    }

    public function test_can_assign_cards_to_distributor(): void
    {
        $cards = $this->cardService->generateCards(3, 50.00, $this->admin);
        $cardIds = collect($cards)->pluck('id')->toArray();

        $count = $this->cardService->assignCardsToDistributor($cardIds, $this->distributor);

        $this->assertEquals(3, $count);
        
        foreach ($cards as $card) {
            $card->refresh();
            $this->assertEquals($this->distributor->id, $card->assigned_to);
        }
    }

    public function test_can_use_card(): void
    {
        $cards = $this->cardService->generateCards(1, 200.00, $this->admin);
        $card = $cards[0];
        $card->update(['assigned_to' => $this->distributor->id]);

        $usedCard = $this->cardService->useCard($card->card_number, $card->pin, $this->customer);

        $this->assertInstanceOf(RechargeCard::class, $usedCard);
        $this->assertEquals('used', $usedCard->status);
        $this->assertEquals($this->customer->id, $usedCard->used_by);
        $this->assertNotNull($usedCard->used_at);
    }

    public function test_cannot_use_invalid_card(): void
    {
        $result = $this->cardService->useCard('INVALID-CARD-NUMBER', '1234', $this->customer);

        $this->assertNull($result);
    }

    public function test_can_get_distributor_summary(): void
    {
        $cards = $this->cardService->generateCards(10, 100.00, $this->admin);
        $this->cardService->assignCardsToDistributor(
            collect($cards)->pluck('id')->toArray(),
            $this->distributor
        );

        // Use 3 cards
        for ($i = 0; $i < 3; $i++) {
            $cards[$i]->update([
                'status' => 'used',
                'used_by' => $this->customer->id,
                'used_at' => now(),
            ]);
        }

        $summary = $this->cardService->getDistributorSummary($this->distributor);

        $this->assertEquals(10, $summary['total_cards']);
        $this->assertEquals(7, $summary['active_cards']);
        $this->assertEquals(3, $summary['used_cards']);
        $this->assertEquals(700.00, $summary['total_value']); // 7 * 100
        $this->assertEquals(300.00, $summary['sold_value']); // 3 * 100
    }
}
