<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CuisineController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\EnumController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StoreOptionController;
use App\Http\Controllers\StoreOptionValueController;
use App\Http\Controllers\PCategoryController;
use App\Http\Controllers\PlatformOptionController;
use App\Http\Controllers\SCategoryController;
use App\Http\Controllers\PriceLevelController;
use App\Http\Controllers\SeatLevelController;
use App\Http\Controllers\ReservationController;
use App\Models\Reservation;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// 🟢 公共访问接口（无需登录）
Route::post('/send-verify-code', [AuthController::class, 'sendVerifyCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
  Route::get('/enums', [EnumController::class, 'index']);

// 需要 Sanctum 登录认证
// 需要登陆所有角色
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/change-password', [AuthController::class, 'change-password']);
  Route::post('/upload', [UploadController::class, 'store']);
});



// admin
Route::middleware(['auth:sanctum', 'header.type:admin'])->group(function () {
    Route::get('/store/get-list', [StoreController::class, 'list']); // 获取全部 STORE 数据
  // Route::get('/get-all', [TableController::class, 'list']); admin 获取全部TABLE数据
  // 柬埔寨省市
  Route::prefix('location')->group(function () {
    Route::get('/get-list', [LocationController::class, 'list']);
    Route::post('/create', [LocationController::class, 'create']);
    Route::post('/update', [LocationController::class, 'update']);
    Route::post('/delete', [LocationController::class, 'delete']);
  });

  Route::prefix('cuisine')->group(function () {
    Route::get('/get-list', [CuisineController::class, 'list']);
    Route::post('/create', [CuisineController::class, 'create']);
    Route::post('/update', [CuisineController::class, 'update']);
    Route::post('/delete', [CuisineController::class, 'delete']);
  });

  Route::prefix('price')->group(function () {
    Route::get('/get-list', [PriceLevelController::class, 'list']);
    Route::post('/create', [PriceLevelController::class, 'create']);
    Route::post('/update', [PriceLevelController::class, 'update']);
    Route::post('/delete', [PriceLevelController::class, 'delete']);
  });

  Route::prefix('seat')->group(function () {
    Route::get('/get-list', [SeatLevelController::class, 'list']);
    Route::post('/create', [SeatLevelController::class, 'create']);
    Route::post('/update', [SeatLevelController::class, 'update']);
    Route::post('/delete', [SeatLevelController::class, 'delete']);
  });

  Route::prefix('p-option')->group(function () {
    Route::get('/tree', [PlatformOptionController::class, 'tree']);
    Route::post('/batch-create', [PlatformOptionController::class, 'batchCreate']);
    Route::post('/create', [PlatformOptionController::class, 'create']);
    Route::post('/update', [PlatformOptionController::class, 'update']);
    Route::post('/delete', [PlatformOptionController::class, 'delete']);
  });


  Route::prefix('p-option-value')->group(function () {
    Route::get('/public-list', [MenuController::class, 'public']);
    Route::post('/create', [MenuController::class, 'create']);
    Route::post('/update', [MenuController::class, 'update']);
    Route::post('/delete', [MenuController::class, 'delete']);
  });

  Route::prefix('p-category')->group(function () {
    Route::get('/get-list', [PCategoryController::class, 'list']);
    Route::post('/create', [PCategoryController::class, 'create']);
    Route::post('/update', [PCategoryController::class, 'update']);
    Route::post('/delete', [PCategoryController::class, 'delete']);
  });
});

// merchant
Route::middleware(['auth:sanctum', 'header.type:merchant'])->group(function () {
  Route::prefix('store')->group(function () {
      Route::get('/get-detail', [StoreController::class, 'detail']);
      Route::post('/create', [StoreController::class, 'create']);
      Route::post('/update', [StoreController::class, 'update']);
      Route::post('/delete', [StoreController::class, 'delete']);
  });


  Route::prefix('table')->group(function () {
      Route::get('/get-list', [TableController::class, 'list']);
      Route::post('/create', [TableController::class, 'create']);
      Route::post('/update', [TableController::class, 'update']);
      Route::post('/delete', [TableController::class, 'delete']);
  });


  Route::prefix('menu')->group(function () {
    Route::get('/get-list', [MenuController::class, 'list']);
    Route::get('/public-list', [MenuController::class, 'public']);
    Route::post('/create', [MenuController::class, 'create']);
    Route::post('/update', [MenuController::class, 'update']);
    Route::post('/delete', [MenuController::class, 'delete']);
  });

  Route::prefix('s-category')->group(function () {
    Route::get('/get-list', [SCategoryController::class, 'list']);
    Route::get('/public-list', [SCategoryController::class, 'public']);
    Route::post('/bind', [SCategoryController::class, 'bind']);
    Route::post('/create', [SCategoryController::class, 'create']);
    Route::post('/update', [SCategoryController::class, 'update']);
    Route::post('/delete', [SCategoryController::class, 'delete']);
  });

  Route::prefix('s-option')->group(function () {
    Route::get('/tree', [StoreOptionController::class, 'tree']);
    Route::get('/public-list', [StoreOptionController::class, 'public']);
    Route::post('/batch-create', [StoreOptionController::class, 'batchCreate']);
    Route::post('/create', [StoreOptionController::class, 'create']);
    Route::post('/update', [StoreOptionController::class, 'update']);
    Route::post('/delete', [StoreOptionController::class, 'delete']);
  });

  Route::prefix('s-option-value')->group(function () {
    Route::get('/public-list', [StoreOptionValueController::class, 'public']);
    Route::post('/create', [StoreOptionValueController::class, 'create']);
    Route::post('/update', [StoreOptionValueController::class, 'update']);
    Route::post('/delete', [StoreOptionValueController::class, 'delete']);
  });

  Route::prefix('reservation')->group(function () {
    Route::get('/get-list', [ReservationController::class, 'list']);
    Route::post('/get-detail', [ReservationController::class, 'detail']);
    Route::post('/create', [ReservationController::class, 'storeCreate']);
    Route::post('/update', [ReservationController::class, 'storeUpdate']);
    Route::post('/accept', function (Request $request, ReservationController $ctrl) {
        $request->merge(['status' => \App\Enums\ReservationStatus::Accepted->value]);
        return $ctrl->updateStatus($request);
    })->name('reservation.accept');

    Route::post('/confirm', function (Request $request, ReservationController $ctrl) {
        $request->merge(['status' => \App\Enums\ReservationStatus::Confirmed->value]);
        return $ctrl->updateStatus($request);
    })->name('reservation.confirm');

    Route::post('/complete', function (Request $request, ReservationController $ctrl) {
        $request->merge(['status' => \App\Enums\ReservationStatus::Completed->value]);
        return $ctrl->updateStatus($request);
    })->name('reservation.complete');

    Route::post('/cancel', function (Request $request, ReservationController $ctrl) {
        $request->merge(['status' => \App\Enums\ReservationStatus::Cancelled->value]);
        return $ctrl->updateStatus($request);
    })->name('reservation.cancel');
  });
});


// user
Route::middleware(['auth:sanctum', 'header.type:user'])->group(function () {
  Route::get('/store/public-list', [StoreController::class, 'public']); // Store List
  Route::get('/table/public-list', [TableController::class, 'public']); // Table List
  Route::get('/menu/public-group', [MenuController::class, 'groupMenus']); // Menu List
  Route::post('/create-reservation', [ReservationController::class, 'create']);
  Route::post('/cancel-reservation', [ReservationController::class, 'cancel']);
  Route::get('/available-slots', [ReservationController::class, 'availableSlots']);
});


// merchant or user
Route::middleware(['auth:sanctum', 'header.type:user,merchant'])->group(function () {
  Route::get('/location/public-list', [LocationController::class, 'list']); // Location List
  Route::get('/cuisine/public-list', [CuisineController::class, 'public']); // Cuisine List 用于搜索
  Route::get('/price/public-list', [PriceLevelController::class, 'public']); // Price List
  Route::get('/seat/public-list', [SeatLevelController::class, 'public']); // Seat List
    Route::get('/home', [StoreController::class, 'list']); // 获取全部 STORE 数据
});

// merchant or admin
Route::middleware(['auth:sanctum', 'header.type:admin,merchant'])->group(function () {
  Route::get('/p-option/public-list', [PlatformOptionController::class, 'public']);
});
