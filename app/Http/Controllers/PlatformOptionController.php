<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OptionService;
use  Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\PlatformOptionValue;
use App\Models\PlatformOption;

class PlatformOptionController extends Controller
{
  protected OptionService $optionService;

  public function __construct(OptionService $optionService) {
      $this->optionService = $optionService;
  }

  public function list() {
    $list = $this->optionService->pList();
    return api_response($list);
  }

  public function public() {
      $list = $this->optionService->pPublic();
      return api_response($list);
  }

  public function create(Request $request) {
    $data = $request->validate([
      'name'      => 'required|string|max:255',
      'type'      => ['required|integer', Rule::in([10, 20])], // 对应 OptionType 枚举值
      'is_active' => 'boolean',
    ]);

    $this->optionService->pCreate($data);

    return api_response();
  }

  public function update(Request $request) {
    $data = $request->validate([
      'id'        => 'required|exists:platform_options,id',
      'name'      => 'sometimes|string|max:255',
      'type'      => ['required|integer', Rule::in([10, 20])], // 对应 OptionType 枚举值
      'is_active' => 'sometimes|boolean',
    ]);

    $this->optionService->pUpdate($data);

    return api_response();
  }

  public function delete(Request $request) {
    $data = $request->validate([
      'id' => 'required|exists:platform_options,id',
    ]);

    $this->optionService->pDelete($data['id']);

    return api_response();
  }

  public function tree() {
    $options = PlatformOption::where('is_active', true)
        ->orderBy('id', 'desc')
        ->with(['values' => function ($q) {
            $q->orderByRaw('COALESCE(sort_order, id) ASC');
        }])
        ->get();

    $tree = $options->map(function ($option) {
        return [
            'id'       => $option->id,
            'name'     => $option->name,
            'type'     => 'platform',
            'children' => $option->values->map(function ($val, $index) {
                return [
                    'id'         => $val->id,
                    'name'       => $val->name,
                    'is_active'  => $val->is_active,
                    'sort_order' => $val->sort_order ?? $index,
                    'type'       => 'platform_value'
                ];
            })->toArray(),
        ];
    });

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

      $this->optionService->pBatchCreate($data['data']);

      return api_response();
  }

}
