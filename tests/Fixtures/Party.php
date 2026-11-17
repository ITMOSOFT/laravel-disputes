<?php

namespace ITMOSOFT\Disputes\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use ITMOSOFT\Disputes\Concerns\HasDisputes;

class Party extends Model
{
    use HasDisputes;

    protected $table = 'test_parties';
    protected $guarded = [];
    public $timestamps = false;
}
