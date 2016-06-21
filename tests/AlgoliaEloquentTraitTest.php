<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Tests\Models\Model2;
use AlgoliaSearch\Tests\Models\Model4;
use AlgoliaSearch\Tests\Models\Model6;
use AlgoliaSearch\Tests\Models\Model7;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase;

class AlgoliaEloquentTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Config::set('algolia.id', 'your-application-id');
        Config::set('algolia.key', 'your-api-key');
    }

    public function testGetAlgoliaRecordDefault()
    {
        $model2 = new Model2();
        $model4 = new Model4();
        $this->assertEquals(array('id2' => 1, 'objectID' => 1), $model2->getAlgoliaRecordDefault());
        $this->assertEquals(array('id2' => 1, 'objectID' => 1, 'id3' => 1, 'name' => 'test'), $model4->getAlgoliaRecordDefault());
    }

    public function testPushToindex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $model4 = new Model4();
        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId($model4));
        $modelHelper->shouldReceive('indexOnly')->andReturn(true);

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('addObject')->times(2)->with($model4->getAlgoliaRecordDefault());

        $this->assertEquals(null, $model4->pushToIndex());
    }

    public function testRemoveFromIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $model4 = new Model4();
        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId($model4));

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('deleteObject')->times(2)->with(1);

        $this->assertEquals(null, $model4->removeFromIndex());
    }

    public function testSetSettings()
    {
        $index = Mockery::mock('\AlgoliaSearch\Index');
        $index->shouldReceive('setSettings')->with(array('slaves' => array('model_6_desc_testing')));
        $index->shouldReceive('setSettings')->with(array('ranking' => array('desc(name)')));

        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');
        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $model6 = new Model6();
        $modelHelper->shouldReceive('getSettings')->andReturn($realModelHelper->getSettings($model6));
        $modelHelper->shouldReceive('getIndices')->andReturn([$index]);
        $modelHelper->shouldReceive('getFinalIndexName')->andReturn($realModelHelper->getFinalIndexName($model6, 'model_6_desc'));
        $modelHelper->shouldReceive('getSlavesSettings')->andReturn($realModelHelper->getSlavesSettings($model6));

        $settings = $realModelHelper->getSettings($model6);
        $this->assertEquals($modelHelper->getFinalIndexName($model6, $settings['slaves'][0]), 'model_6_desc_testing');

        $model6->setSettings();
    }

    public function testSetSynonyms()
    {
        $index = Mockery::mock('\AlgoliaSearch\Index');
        $index->shouldReceive('batchSynonyms')->with(
            [
                [
                    'objectID' => 'red-color',
                    'type'     => 'synonym',
                    'synonyms' => [
                        'red',
                        'really red',
                        'much red'
                    ]
                ]
            ],
            true,
            true
        );

        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');
        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $model7 = new Model7();
        $modelHelper->shouldReceive('getSettings')->andReturn($realModelHelper->getSettings($model7));
        $modelHelper->shouldReceive('getIndices')->andReturn([$index]);
        $modelHelper->shouldReceive('getSlavesSettings')->andReturn($realModelHelper->getSlavesSettings($model7));

        $this->assertEquals(null, $model7->setSettings());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
