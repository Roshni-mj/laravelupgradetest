<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participanttochapter extends Model
{
    protected $fillable = ['chapter_id','participant_id'];
}
