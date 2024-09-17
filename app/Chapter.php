<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['chapter_name','truedialog_chapterlist_id', 'chapter_type'];
}
