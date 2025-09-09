<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class OpFriend extends Model
{
    /** @use HasFactory<\Database\Factories\OpFriendFactory> */
    use HasSnowflakePrimary;
    public function op1()   { return $this->belongsTo(Op::class, 'op_1_id'); }
    public function op2()   { return $this->belongsTo(Op::class, 'op_2_id'); }
    public function sender(){ return $this->belongsTo(Op::class, 'sender_id'); }

}
