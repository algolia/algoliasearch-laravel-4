<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Tests\Models\Model1;
use AlgoliaSearch\Tests\Models\Model2;
use AlgoliaSearch\Tests\Models\Model3;
use AlgoliaSearch\Tests\Models\Model4;
use AlgoliaSearch\Tests\Models\Model5;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

class ModelHelperTest extends TestCase
{
    /** @var \AlgoliaSearch\Laravel\ModelHelper modelHelper */
    private $modelHelper;

    public function setUp()
    {
        parent::setUp();

        Config::set('algolia.id', 'your-application-id');
        Config::set('algolia.key', 'your-api-key');

        $this->modelHelper = $this->app->make('\AlgoliaSearch\Laravel\ModelHelper');
    }

    public function testAutoIndexAndAutoDelete()
    {
        $this->assertEquals(true, $this->modelHelper->isAutoIndex(new Model1()));
        $this->assertEquals(false, $this->modelHelper->isAutoIndex(new Model2()));
        $this->assertEquals(false, $this->modelHelper->isAutoIndex(new Model3()));

        $this->assertEquals(true, $this->modelHelper->isAutoDelete(new Model1()));
        $this->assertEquals(false, $this->modelHelper->isAutoDelete(new Model2()));
        $this->assertEquals(false, $this->modelHelper->isAutoDelete(new Model3()));
    }

    public function testGetKey()
    {
        $this->assertEquals(null, $this->modelHelper->getKey(new Model1()));
        $this->assertEquals(1, $this->modelHelper->getKey(new Model2()));
    }

    public function testIndexOnly()
    {
        $this->assertEquals(true, $this->modelHelper->indexOnly(new Model1(), 'test'));
        $this->assertEquals(true, $this->modelHelper->indexOnly(new Model2(), 'test'));
        $this->assertEquals(false, $this->modelHelper->indexOnly(new Model2(), 'test2'));
    }

    public function testGetObjectIds()
    {
        $this->assertEquals('id', $this->modelHelper->getObjectIdKey(new Model1()));
        $this->assertEquals('id2', $this->modelHelper->getObjectIdKey(new Model2()));
        $this->assertEquals('id3', $this->modelHelper->getObjectIdKey(new Model4()));

        $this->assertEquals(1, $this->modelHelper->getObjectId(new Model2()));
        $this->assertEquals(1, $this->modelHelper->getObjectId(new Model4()));
    }

    public function testGetIndices()
    {
        $indices1 = $this->modelHelper->getIndices(new Model1());
        $indices1 = $indices1[0];
        $indices2 = $this->modelHelper->getIndices(new Model5());
        $indices2 = $indices2[0];
        $indices3 = $this->modelHelper->getIndices(new Model1(), 'test');
        $indices3 = $indices3[0];
        $indices4 = $this->modelHelper->getIndices(new Model5(), 'test');
        $indices4 = $indices4[0];
        $indices5 = $this->modelHelper->getIndices(new Model4());
        $indices5 = $indices5[0];

        $this->assertEquals('model1s', $indices1->indexName);
        $this->assertEquals('model5s_testing', $indices2->indexName);
        $this->assertEquals('test', $indices3->indexName);
        $this->assertEquals('test_testing', $indices4->indexName);
        $this->assertEquals('model4s', $indices5->indexName);

        $indices = $this->modelHelper->getIndices(new Model2());
        $indices6 = $indices[0];

        $this->assertEquals(2, count($indices));
        $this->assertEquals('index1', $indices6->indexName);
    }
}
