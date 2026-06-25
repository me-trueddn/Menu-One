<?php

namespace App\DataTransferObjects;

class NormalizedOrderDto
{
    /**
     * @param  list<array{external_id: ?string, name: string, qty: int, unit_price: float, notes: ?string}>  $items
     * @param  array<string, mixed>  $rawPayload
     */
    public function __construct(
        public string $externalOrderId,
        public ?string $customerName = null,
        public ?string $customerPhone = null,
        public ?string $deliveryNote = null,
        public bool $paymentCollectedExternally = true,
        public array $items = [],
        public array $rawPayload = [],
    ) {}
}
