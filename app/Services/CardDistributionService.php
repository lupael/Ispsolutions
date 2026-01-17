<?php

namespace App\Services;

use App\Models\RechargeCard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CardDistributionService
{
    /**
     * Generate recharge cards in bulk
     */
    public function generateCards(int $quantity, float $denomination, User $generatedBy, ?Carbon $expiresAt = null): array
    {
        $cards = [];
        
        DB::transaction(function () use ($quantity, $denomination, $generatedBy, $expiresAt, &$cards) {
            for ($i = 0; $i < $quantity; $i++) {
                $cards[] = RechargeCard::create([
                    'tenant_id' => $generatedBy->tenant_id,
                    'card_number' => $this->generateCardNumber(),
                    'pin' => $this->generatePin(),
                    'denomination' => $denomination,
                    'status' => 'active',
                    'generated_by' => $generatedBy->id,
                    'expires_at' => $expiresAt ?? now()->addYear(),
                ]);
            }
        });

        return $cards;
    }

    /**
     * Assign cards to a distributor
     */
    public function assignCardsToDistributor(array $cardIds, User $distributor): int
    {
        return RechargeCard::whereIn('id', $cardIds)
            ->whereNull('assigned_to')
            ->where('status', 'active')
            ->update(['assigned_to' => $distributor->id]);
    }

    /**
     * Use a recharge card
     */
    public function useCard(string $cardNumber, string $pin, User $customer): ?RechargeCard
    {
        return DB::transaction(function () use ($cardNumber, $pin, $customer) {
            $card = RechargeCard::where('card_number', $cardNumber)
                ->where('pin', $pin)
                ->where('status', 'active')
                ->where('tenant_id', $customer->tenant_id)
                ->lockForUpdate()
                ->first();

            if (!$card || !$card->isAvailable()) {
                return null;
            }

            $card->update([
                'status' => 'used',
                'used_by' => $customer->id,
                'used_at' => now(),
            ]);

            // Credit the amount to customer account (this would integrate with billing)
            $this->creditCustomerAccount($customer, $card->denomination);

            return $card;
        });
    }

    /**
     * Get distributor card summary
     */
    public function getDistributorSummary(User $distributor): array
    {
        $cards = RechargeCard::where('assigned_to', $distributor->id);

        return [
            'total_cards' => $cards->count(),
            'active_cards' => $cards->where('status', 'active')->count(),
            'used_cards' => $cards->where('status', 'used')->count(),
            'total_value' => $cards->where('status', 'active')->sum('denomination'),
            'sold_value' => $cards->where('status', 'used')->sum('denomination'),
        ];
    }

    /**
     * Generate unique card number
     */
    protected function generateCardNumber(): string
    {
        do {
            $cardNumber = 'RC-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (RechargeCard::where('card_number', $cardNumber)->exists());

        return $cardNumber;
    }

    /**
     * Generate PIN
     */
    protected function generatePin(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Credit customer account
     */
    protected function creditCustomerAccount(User $customer, float $amount): void
    {
        // This would integrate with the billing system
        // For now, we'll just create a payment record
        // In a real system, this would update customer balance or generate credit
    }

    /**
     * Cancel expired cards
     */
    public function cancelExpiredCards(): int
    {
        return RechargeCard::where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', today())
            ->update(['status' => 'expired']);
    }
}
