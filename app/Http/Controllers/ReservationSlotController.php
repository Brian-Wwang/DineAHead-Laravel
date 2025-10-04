<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationSlot;
use Illuminate\Http\Request;
use App\Services\ReservationService;

class ReservationSlotController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * list slots for a reservation
     */
    public function list(Reservation $reservation)
    {
        return $this->reservationService->listSlots($reservation);
    }

    /**
     * add a new slot
     */
    public function create(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        return $this->reservationService->addSlot($reservation, $data);
    }

    /**
     * update slot
     */
    public function update(Request $request, ReservationSlot $slot)
    {
        $data = $request->validate([
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        return $this->reservationService->updateSlot($slot, $data);
    }

    /**
     * delete slot
     */
    public function delete(ReservationSlot $slot)
    {
        $this->reservationService->deleteSlot($slot);
        return response()->json(['success' => true, 'message' => 'Reservation slot deleted']);
    }
}
