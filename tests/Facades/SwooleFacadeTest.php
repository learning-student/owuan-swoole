<?php

namespace SwooleTW\Http\Tests\Facades;

use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Table\Facades\SwooleTable;
use SwooleTW\Http\Tests\LaravelTestCase;

class SwooleFacadeTest extends LaravelTestCase
{


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFacadeReturnsCorrectInstance()
    {
        $instance = SwooleTable::getFacadeRoot();

        $this->assertInstanceOf(\SwooleTW\Http\Table\SwooleTable::class, $instance);
    }
}
