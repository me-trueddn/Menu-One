<?php

namespace App\Http\Controllers\Kitchen;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function __construct(protected KitchenService $kitchen) {}

    public function index(): View
    {
        $tables = $this->kitchen->pendingGroupedByTable();

        return view('theme::pages.kitchen.index', compact('tables'));
    }

    public function poll(): JsonResponse
    {
        $tables = $this->kitchen->pendingGroupedByTable()->map(fn (array $group) => [
            'table_id' => $group['table_id'],
            'table' => $group['table'],
            'since' => $group['since']->format('H:i'),
            'items' => $group['items']->map(fn (OrderItem $item) => $this->serializeItem($item))->values(),
        ]);

        return response()->json(['tables' => $tables]);
    }

    public function updateStatus(Request $request, OrderItem $item): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:preparing,ready'],
        ]);

        $status = OrderItemStatus::from($validated['status']);
        $updated = $this->kitchen->updateItemStatus($item, $status);

        return response()->json([
            'success' => true,
            'item' => $this->serializeItem($updated),
        ]);
    }

    /** @return array<string, mixed> */
    protected function serializeItem(OrderItem $item): array
    {
        return [
            'id' => $item->id,
            'table' => $item->order->cafeTable->name,
            'product' => $item->product->name,
            'qty' => $item->qty,
            'status' => $item->status->value,
            'status_label' => $item->status->label(),
            'notes' => $item->notes,
            'created_at' => $item->created_at->format('H:i'),
        ];
    }
}
