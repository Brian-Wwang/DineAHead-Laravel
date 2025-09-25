<?php

namespace App\Http\Controllers;

use App\Enums\MenuStatus;
use App\Enums\DiscountType;
use Illuminate\Http\Request;

class EnumController extends Controller
{
    public function index()
    {
      return api_response([
        'menu_status'    => MenuStatus::toArray(),
        'discount_type'  => DiscountType::toArray(),
      ]);
    }
}
