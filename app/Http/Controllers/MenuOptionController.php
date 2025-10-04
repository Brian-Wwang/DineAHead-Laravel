<?php

namespace App\Http\Controllers;

use App\Models\MenuOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuOptionController extends Controller
{
  public function attachOptions(Request $request, $menuId)
  {
      $validated = $request->validate([
          'options' => 'required|array',
          'options.*.platform_option_id' => 'nullable|exists:platform_options,id',
          'options.*.store_option_id' => 'nullable|exists:store_options,id',
          'options.*.is_required' => 'boolean',
          'options.*.max_select' => 'integer|min:1',
      ]);

      DB::transaction(function () use ($menuId, $validated) {
          MenuOption::where('menu_id', $menuId)->delete();

          foreach ($validated['options'] as $opt) {
              MenuOption::create([
                  'menu_id' => $menuId,
                  'platform_option_id' => $opt['platform_option_id'] ?? null,
                  'store_option_id' => $opt['store_option_id'] ?? null,
                  'is_required' => $opt['is_required'] ?? false,
                  'max_select' => $opt['max_select'] ?? 1,
              ]);
          }
      });

      return response()->json(['success' => true, 'message' => '选项绑定成功']);
  }

  public function getOptions($menuId)
  {
      $options = \App\Models\MenuOption::with([
          'platformOption.values',
          'storeOption.values'
      ])->where('menu_id',$menuId)->get();

      return response()->json($options);
  }
}
