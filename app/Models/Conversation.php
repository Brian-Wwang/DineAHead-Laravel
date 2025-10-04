<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['type','title'];

    public function participants(): HasMany {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages(): HasMany {
        return $this->hasMany(Message::class)->latest('id');
    }
}
