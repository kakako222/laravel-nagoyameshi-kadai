<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * 予約一覧ページを表示
     */
    public function index()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->orderBy('reserved_datetime', 'desc')
            ->paginate(15);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * 予約ページを表示
     */
    public function create(Restaurant $restaurant)
    {
        return view('reservations.create', compact('restaurant'));
    }

    /**
     * 予約機能
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'reservation_date' => 'required|date_format:Y-m-d',
            'reservation_time' => 'required|date_format:H:i',
            'number_of_people' => 'required|integer|min:1|max:50',
        ]);

        $reservedDatetime = $request->input('reservation_date') . ' ' . $request->input('reservation_time');

        Reservation::create([
            'reserved_datetime' => $reservedDatetime,
            'number_of_people' => $request->input('number_of_people'),
            'restaurant_id' => $restaurant->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('reservations.index')->with('flash_message', '予約が完了しました。');
    }

    /**
     * 予約キャンセル機能
     */
    public function destroy(Reservation $reservation)
    {
        if ($reservation->user_id !== Auth::id()) {
            return redirect()->route('reservations.index')->with('error_message', '不正なアクセスです。');
        }

        $reservation->delete();

        return redirect()->route('reservations.index')->with('flash_message', '予約をキャンセルしました。');
    }
}
