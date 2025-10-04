<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\DiscountType;
use App\Enums\LocationType;
use App\Enums\OptionType;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use Illuminate\Http\Request;

class EnumController extends Controller
{
    public function index()
    {
      return api_response([
        'reservation_status' => ReservationStatus::toArray(),
        'discount_type'  => DiscountType::toArray(),
        'location_type' => LocationType::toArray(),
        'option_type' => OptionType::toArray(),
        'category_type' => CategoryType::toArray(),
        'payment_code' => PaymentStatus::toArray()
      ]);
    }
}
