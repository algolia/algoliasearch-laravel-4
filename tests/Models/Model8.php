<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model8 extends Model
{
    use AlgoliaEloquentTrait;

    public static $autoIndex = false;
    public static $autoDelete = false;

    protected $primaryKey = 'id';

    public $indices = array('index1', 'index2');

    public function __construct()
    {
        $this->id = 1;
    }

    public function getAlgoliaRecord($indexName)
    {
        if ($indexName === 'index1') {
            $extraData = ['key' => 'someKey'];
        } else {
            $extraData = ['name' => 'someName'];
        }

        return array_merge($this->toArray(), $extraData);
    }
}
