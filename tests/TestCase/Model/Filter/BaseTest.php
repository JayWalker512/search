<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Test\TestApp\Model\Filter\TestFilter;
use Search\Test\TestApp\Model\TestRepository;

class BaseTest extends TestCase
{
    /**
     * @var \Search\Manager
     */
    public $Manager;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setup()
    {
        $table = TableRegistry::get('Articles');
        $this->Manager = new Manager($table);
    }

    /**
     * @return array
     */
    public function emptyDataProvider()
    {
        return [
            [''],
            [null],
            [[]],
            [['']]
        ];
    }

    /**
     * @dataProvider emptyDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The `field` option is invalid. Expected a non-empty string or array.
     * @param mixed $emptyValue Empty value.
     * @return void
     */
    public function testConstructEmptyFieldOption($emptyValue)
    {
        new TestFilter(
            'name',
            $this->Manager,
            ['field' => $emptyValue]
        );
    }

    /**
     * @dataProvider emptyDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The `$name` argument is invalid. Expected a non-empty string.
     * @param mixed $emptyValue Empty value.
     * @return void
     */
    public function testConstructEmptyNameArgument($emptyValue)
    {
        new TestFilter(
            $emptyValue,
            $this->Manager,
            ['field' => 'field']
        );
    }

    /**
     * @return array
     */
    public function nonEmptyFieldDataProvider()
    {
        return [
            ['0'], ['value'], [['value']]
        ];
    }

    /**
     * @dataProvider nonEmptyFieldDataProvider
     * @param mixed $nonEmptyValue Non empty value.
     * @return void
     */
    public function testConstructNonEmptyFieldOption($nonEmptyValue)
    {
        $filter = new TestFilter(
            'name',
            $this->Manager,
            ['field' => $nonEmptyValue, 'aliasField' => false]
        );
        $this->assertEquals($filter->field(), $nonEmptyValue);
    }

    /**
     * @return array
     */
    public function nonEmptyNameDataProvider()
    {
        return [
            ['0'], ['value']
        ];
    }

    /**
     * @dataProvider nonEmptyNameDataProvider
     * @param mixed $nonEmptyValue Non empty value.
     * @return void
     */
    public function testConstructNonEmptyNameArgument($nonEmptyValue)
    {
        $filter = new TestFilter(
            $nonEmptyValue,
            $this->Manager,
            ['field' => 'field']
        );
        $this->assertEquals($filter->name(), $nonEmptyValue);
    }

    /**
     * @return void
     */
    public function testSkip()
    {
        $filter = new TestFilter(
            'field',
            $this->Manager,
            ['alwaysRun' => true, 'filterEmpty' => true]
        );

        $filter->args(['field' => '1']);
        $this->assertFalse($filter->skip());

        $filter->args(['field' => '0']);
        $this->assertFalse($filter->skip());

        $filter->args(['field' => '']);
        $this->assertTrue($filter->skip());

        $filter->args(['field' => []]);
        $this->assertTrue($filter->skip());
    }

    /**
     * @return void
     */
    public function testValue()
    {
        $filter = new TestFilter(
            'field',
            $this->Manager,
            ['defaultValue' => 'default']
        );

        $filter->args(['field' => 'value']);
        $this->assertEquals('value', $filter->value());

        $filter->args(['other_field' => 'value']);
        $this->assertEquals('default', $filter->value());

        $filter->args(['field' => ['value1', 'value2']]);
        $this->assertEquals('default', $filter->value());

        $filter->config('multiValue', true);
        $filter->args(['field' => ['value1', 'value2']]);
        $this->assertEquals(['value1', 'value2'], $filter->value());
    }

    /**
     * @return void
     */
    public function testFieldAliasing()
    {
        $filter = new TestFilter(
            'field',
            $this->Manager,
            []
        );

        $this->assertEquals('Articles.field', $filter->field());

        $filter->config('aliasField', false);
        $this->assertEquals('field', $filter->field());

        $filter = new TestFilter(
            'name',
            $this->Manager,
            ['field' => ['field1', 'field2']]
        );

        $expected = ['Articles.field1', 'Articles.field2'];
        $this->assertEquals($expected, $filter->field());
    }

    /**
     * @return void
     */
    public function testFieldAliasingWithNonSupportingRepository()
    {
        $filter = new TestFilter(
            'field',
            new Manager(new TestRepository()),
            ['aliasField' => true]
        );

        $this->assertEquals('field', $filter->field());
    }
}
