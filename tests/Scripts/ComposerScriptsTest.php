<?php


namespace SwooleTW\Http\Tests\Scripts;

use Mockery;
use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Scrips\CheckFileForHeaderFunctions;
use SwooleTW\Http\Scrips\ComposerScripts;
use SwooleTW\Http\Scrips\MonkeyPatching;

class ComposerScriptsTest extends TestCase
{


    /**
     * @covers \SwooleTW\Http\Scrips\ComposerScripts::postUpdate
     * @covers \SwooleTW\Http\Scrips\ComposerScripts::runPostUpdate
     */
    public function testComposerPostUpdateCreatesFilesSuccessfully()
    {
        $mock = new MonkeyPatching();

        $mock->setPatchinDir($this->getPatchingDir());

        $packageFile = dirname(__DIR__) . '/fixtures/packages/Test.php';

        $allFiles = [
            $packageFile
        ];

        $this->assertFileExists($packageFile);

        ComposerScripts::runPostUpdate($allFiles, $mock);

        $patchingFile = dirname(__DIR__) . '/fixtures/patcing/owuan_swoole_test.php';

        // file should exists
        $this->assertFileExists($patchingFile);


    }


    public function testPatchingContentIsSame()
    {
        $patchingFile = dirname(__DIR__) . '/fixtures/patcing/owuan_swoole_test.php';

        // file should be readable
        $this->assertFileIsReadable($patchingFile);

        $content = file_get_contents($patchingFile);

        $should = <<<'CODE'
<?php

namespace Owuan\Swoole\Test;
use  SwooleTW\Http\Helpers\Response;        

    
if(!function_exists('Owuan\Swoole\Test\header')){
    function header(string $val, bool $replace = true){
        return Response::header($val, $replace);
    }
}

if(!function_exists('Owuan\Swoole\Test\header')){

    function http_response_code($code){
        return Response::httpResponseCode($code);
    }
}


if(!function_exists('Owuan\Swoole\Test\setcookie'))
{
    function setcookie(
        string $name, 
        string $value, 
        int $expires = 0, 
        string $path = "",
        string $domain = "",
        bool $secure = false,
        bool $httpOnly = false
    ) : bool {
        return Response::setCookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }
}

CODE;


        $this->assertSame($should, $content);
    }

    /**
     * @covers \SwooleTW\Http\Scrips\ComposerScripts::runPostUninstalScript
     * @covers \SwooleTW\Http\Scrips\ComposerScripts::preUninstall
     */
    public function testComposerPostUninstallRemovesFiles()
    {
        $mock = Mockery::mock(MonkeyPatching::class);

        $mock->shouldReceive('getPatchinDir')
            ->andReturn($this->getPatchingDir());

        $packageFile = dirname(__DIR__) . '/fixtures/packages/Test.php';


        $patchingFile = dirname(__DIR__) . '/fixtures/patcing/owuan_swoole_test.php';


        $this->assertFileExists($packageFile);
        $this->assertFileExists($patchingFile);


        ComposerScripts::runPostUninstalScript([
            $packageFile
        ], $mock);

        $this->assertFileNotExists(
            $patchingFile
        );
    }


    public function getPatchingDir()
    {
        return dirname(__DIR__) . '/fixtures/patcing/';
    }

    public function getFixtureDir()
    {
        return dirname(__DIR__) . '/fixtures';
    }

}