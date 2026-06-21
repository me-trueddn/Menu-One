<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\DiningTable;
use App\Models\TableReservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, [10, 50, 150], true)) {
            $perPage = 10;
        }

        $reservations = TableReservation::query()
            ->with(['cafeTable', 'user'])
            ->where(function ($query) {
                $query->upcoming()
                    ->orWhere(function ($q) {
                        $q->where('status', ReservationStatus::Completed)
                            ->where('updated_at', '>=', now()->subDays(7));
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('guest_name', 'like', '%'.$search.'%')
                        ->orWhere('guest_phone', 'like', '%'.$search.'%')
                        ->orWhereHas('cafeTable', fn ($table) => $table->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ReservationStatus::Active->value])
            ->orderBy('starts_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('theme::pages.reservations.index', compact('reservations', 'search', 'perPage'));
    }

    public function create(Request $request): View
    {
        $tables = DiningTable::query()->orderBy('name')->get();
        $selectedTableId = $request->query('table');

        return view('theme::pages.reservations.create', compact('tables', 'selectedTableId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $table = DiningTable::findOrFail($validated['cafe_table_id']);

        try {
            $this->reservations->create(
                $table,
                $this->authUser(),
                $validated['guest_name'],
                (int) $validated['party_size'],
                Carbon::parse($validated['starts_at']),
                Carbon::parse($validated['ends_at']),
                $validated['guest_phone'] ?? null,
                $validated['notes'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('reservations.index')
            ->with('success', __('menu.reservation_created'));
    }

    public function edit(TableReservation $reservation): View|RedirectResponse
    {
        if ($reservation->status !== ReservationStatus::Active) {
            return redirect()
                ->route('reservations.index')
                ->with('error', __('menu.reservation_not_active'));
        }

        $tables = DiningTable::query()->orderBy('name')->get();

        return view('theme::pages.reservations.edit', compact('reservation', 'tables'));
    }

    public function update(Request $request, TableReservation $reservation): RedirectResponse
    {
        if ($reservation->status !== ReservationStatus::Active) {
            return back()->with('error', __('menu.reservation_not_active'));
        }

        $validated = $this->validated($request);
        $table = DiningTable::findOrFail($validated['cafe_table_id']);

        try {
            $this->reservations->update(
                $reservation,
                $table,
                $validated['guest_name'],
                (int) $validated['party_size'],
                Carbon::parse($validated['starts_at']),
                Carbon::parse($validated['ends_at']),
                $validated['guest_phone'] ?? null,
                $validated['notes'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('menu.reservation_updated'));
    }

    public function destroy(TableReservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->cancel($reservation);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('menu.reservation_cancelled_success'));
    }

    public function complete(TableReservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->complete($reservation);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('menu.reservation_completed_success'));
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'cafe_table_id' => ['required', 'exists:cafe_tables,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:30'],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
