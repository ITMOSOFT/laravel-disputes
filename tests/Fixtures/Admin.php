<?php

namespace ITMOSOFT\Disputes\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'test_admins';
    protected $guarded = [];
    public $timestamps = false;
}
