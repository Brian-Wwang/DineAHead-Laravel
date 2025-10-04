<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OptionService;
use  Illuminate\Validation\Rule;

class StoreOptionController extends Controller
{
  protected OptionService $optionService;

  public function __construct(OptionService $optionService) {
    $this->optionService = $optionService;
  }

  // public function list() {
  //   $user = Auth::user();
  //   $storeId = $user->store->id ?? null;
  //   if (!$storeId) {
  //     return api_response(null, 'Please Create Store', 403, false);
  //   }

  //   $list = $this->optionService->sList();
  //   return api_response($list);
  // }

  public function public() {
    $user = Auth::user();
    $storeId = $user->store->id ?? null;
    if (!$storeId) {
      return api_response(null, 'Please Create Store', 403, false);
    }

    $list = $this->optionService->sPublic();
    return api_response($list);
  }

  public function tree() {
    $user = Auth::user();
    $storeId = $user->store->id ?? null;
    if (!$storeId) {
      return api_response(null, 'Please Create Store', 403, false);
    }

    $tree = $this->optionService->sTree();
    return api_response($tree);
  }

  public function batchCreate(Request $request) {
    $data = $request->validate([
      'data'                => 'required|array',
      'data*.parent.name'       => 'required|string|max:255',
      'data*.parent.type'       => ['required','integer', Rule::in([10,20])],
      'data*.parent.is_active'  => 'nullable|boolean',
      'data*.children'          => 'nullable|array',
      'data*.children.*.name'   => 'required|string|max:255',
      'data*.children.*.extra_price' => 'nullable|numeric',
      'data*.children.*.sort_order'  => 'nullable|integer',
      'data*.children.*.is_active'   => 'nullable|boolean',
    ]);

    $this->optionService->batchCreate($data['data'], $request->user());

    return api_response();
  }

  public function create(Request $request) {
    $user = Auth::user();
    $storeId = $user->store->id ?? null;
    if (!$storeId) {
      return api_response(null, 'Please Create Store', 403, false);
    }

    $data = $request->validate([
      'name' => 'required|string|max:50',
      'type' => 'nullable|in:10,20'
    ]);

    $this->optionService->sCreate($data);
    return api_response();
  }

  public function update(Request $request, $id) {
    $user = Auth::user();
    $storeId = $user->store->id ?? null;
    if (!$storeId) {
      return api_response(null, 'Please Create Store', 403, false);
    }

    $data = $request->validate([
      'name'      => 'required|string|max:50',
      'type'      => 'nullable|in:10,20',
      'is_active' => 'boolean'
    ]);
    $data['id'] = $id;

    $this->optionService->sUpdate($data);
    return api_response();
  }

  public function delete(Request $request) {
    $user = Auth::user();
    $storeId = $user->store->id ?? null;
    $data = $request->validate([
      'id' => 'required|exists:store_options,id',
    ]);
    if (!$storeId) {
      return api_response(null, 'Please Create Store', 403, false);
    }

    $this->optionService->sDelete($data['id']);
    return api_response();
  }
}
