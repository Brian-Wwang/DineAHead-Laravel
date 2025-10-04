<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuOption extends Model
{
    protected $fillable = [
        'menu_id','platform_option_id','store_option_id','is_required','max_select'
    ];

    public function platformOption()
    {
        return $this->belongsTo(PlatformOption::class);
    }

    public function storeOption()
    {
        return $this->belongsTo(StoreOption::class);
    }
}
