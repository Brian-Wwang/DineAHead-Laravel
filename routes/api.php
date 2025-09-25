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


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// 🟢 公共访问接口（无需登录）
Route::post('/send_verify_code', [AuthController::class, 'sendVerifyCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset_password', [AuthController::class, 'resetPassword']);
  Route::get('/enums', [EnumController::class, 'index']);

// 需要 Sanctum 登录认证
// 需要登陆所有角色
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/change_password', [AuthController::class, 'change_password']);
  Route::post('/upload', [UploadController::class, 'store']);
});



// admin
Route::middleware(['auth:sanctum', 'header.type:admin'])->group(function () {
    Route::get('/store/list', [StoreController::class, 'list']); // 获取全部 STORE 数据
  // Route::get('/get_all', [TableController::class, 'list']); admin 获取全部TABLE数据
  // 柬埔寨省市
  Route::prefix('location')->group(function () {
    Route::get('/list', [LocationController::class, 'list']);
    Route::post('/create', [LocationController::class, 'create']);
    Route::post('/update', [LocationController::class, 'update']);
    Route::post('/delete', [LocationController::class, 'delete']);
  });

  Route::prefix('cuisine')->group(function () {
    Route::get('/get_list', [CuisineController::class, 'list']);
    Route::post('/create', [CuisineController::class, 'create']);
    Route::post('/update', [CuisineController::class, 'update']);
    Route::post('/delete', [CuisineController::class, 'delete']);
  });
});

// merchant
Route::middleware(['auth:sanctum', 'header.type:merchant'])->group(function () {
  Route::prefix('store')->group(function () {
      Route::get('/get_detail', [StoreController::class, 'detail']);
      Route::post('/create', [StoreController::class, 'create']);
      Route::post('/update', [StoreController::class, 'update']);
      Route::post('/delete', [StoreController::class, 'delete']);
  });


  Route::prefix('table')->group(function () {
      Route::get('/get_list', [TableController::class, 'list']);
      Route::post('/create', [TableController::class, 'create']);
      Route::post('/update', [TableController::class, 'update']);
      Route::post('/delete', [TableController::class, 'delete']);
  });


  Route::prefix('menu')->group(function () {
    Route::get('/get_list', [MenuController::class, 'list']);
    Route::post('/create', [MenuController::class, 'create']);
    Route::post('/update', [MenuController::class, 'update']);
    Route::post('/delete', [MenuController::class, 'delete']);
  });

});


// user
Route::middleware(['auth:sanctum', 'header.type:user'])->group(function () {
  Route::get('/store/public_list', [StoreController::class, 'public']); // Store List
  Route::get('/table/public_list', [TableController::class, 'public']); // Table List
  Route::get('/menu/public_list', [MenuController::class, 'public']); // Menu List
});


// merchant or user
Route::middleware(['auth:sanctum', 'header.type:user,merchant'])->group(function () {
  Route::get('/location/list', [LocationController::class, 'list']); // Location List
  Route::get('/cuisine/public_list', [CuisineController::class, 'public']); // Cuisine List 用于搜索
});


// 需要 Sanctum 登录 + 仅限 merchant 访问
// Route::middleware(['header.type:merchant'])->group(function () {
//     Route::get('/merchant-only', [TestController::class, 'merchantOnly']);
// });

// 需要 Sanctum 登录 + 仅限 admin 或 merchant
// Route::middleware(['header.type:admin,merchant'])->group(function () {
//     Route::get('/admin-or-merchant', [TestController::class, 'adminOrMerchant']);
// });
