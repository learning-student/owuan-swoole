<?php


namespace SwooleTW\Http\Tests\Scripts;

use SwooleTW\Http\Scrips\MonkeyPatching;
use SwooleTW\Http\Tests\TestCase;

/**
 * Class MonkeyPatchingTest
 * @package SwooleTW\Http\Tests\Scripts
 * @covers \SwooleTW\Http\Scrips\MonkeyPatching
 */
class MonkeyPatchingTest extends TestCase
{

    /**
     * @throws \ReflectionException
     */
    public function testConstructorCallsInternalMethods()
    {
        $classname = MonkeyPatching::class;

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setPatchinDir');

        // now call the constructor
        $reflectedClass = new \ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock);
    }

    /**
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::setPatchinDir
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::getPatchinDir
     */
    public function testGetPatchingDirReturnsCorrect()
    {

        $monkey = new MonkeyPatching();

        $monkey->setPatchinDir(__DIR__);

        $this->assertSame(__DIR__, $monkey->getPatchinDir());
    }

    /**
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::createStubName
     */
    public function testCreateStubnameCreatesCorrectName()
    {

        $monkey = new MonkeyPatching();

        $this->assertSame(
            $monkey->createStubName('Owuan\Swoole\Test'),
            'owuan_swoole_test.php'
        );
    }

    /**
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::createStub
     */
    public function testCreateStubCreatesCorrectContent()
    {
        $monkey = new MonkeyPatching();

        $namespace = 'Owuan\Swoole\Test';

        $monkey->setPatchinDir(dirname(__DIR__) . '/fixtures/patcing/');
        $monkey->createStub($namespace);


        $file = $monkey->getPatchinDir() . $monkey->createStubName($namespace);

        $this->assertFileExists($file);
    }

    /**
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::autoloadAllPatchingFiles
     */
    public function testAutoloadSuccess()
    {
        $monkey = new MonkeyPatching();
        $monkey->setPatchinDir(dirname(__DIR__) . '/fixtures/patcing/');

        $monkey->autoloadAllPatchingFiles();

        $exists = function_exists('Owuan\Swoole\Test\header');

        $this->assertTrue($exists);
    }


    /**
     * @covers \SwooleTW\Http\Scrips\MonkeyPatching::removeOldStub
     */
    public function testRemoveOldStub()
    {
        $monkey = new MonkeyPatching();
        $monkey->setPatchinDir(dirname(__DIR__) . '/fixtures/patcing/');

        $namespace = 'Owuan\Swoole\Test';

        $response = $monkey->removeOldStub($namespace);

        $this->assertTrue($response);
        $this->assertFileNotExists($monkey->getPatchinDir() . $monkey->createStubName($namespace));
    }
}