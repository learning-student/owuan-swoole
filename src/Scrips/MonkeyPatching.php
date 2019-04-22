<?php


namespace SwooleTW\Http\Scrips;


class MonkeyPatching
{

    private $patchinDir;

    protected $stub = <<<'CODE'
    
if(!function_exists('{name}\header')){
    function header(string $val, bool $replace = true){
        return Response::header($val, $replace);
    }
}

if(!function_exists('{name}\header')){

    function http_response_code($code){
        return Response::httpResponseCode($code);
    }
}


if(!function_exists('{name}\setcookie'))
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


    private $namespaceStub = <<<'CODE'
<?php

namespace {namespace};
use  SwooleTW\Http\Helpers\Response;        

CODE;

    /**
     * MonkeyPatching constructor.
     */
    public function __construct()
    {
        $this->setPatchinDir(dirname(__DIR__, 2) .
            DIRECTORY_SEPARATOR .
            'patching' .
            DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public function getPatchinDir(): string
    {
        return $this->patchinDir;
    }

    /**
     * @param string $patchinDir
     * @return MonkeyPatching
     */
    public function setPatchinDir(string $patchinDir): MonkeyPatching
    {
        $this->patchinDir = $patchinDir;
        return $this;
    }


    /**
     * autoload all auto-generated patching files
     */
    public function autoloadAllPatchingFiles()
    {
        $dir = $this->patchinDir;

        $phpFiles = glob("$dir/*.php");

        foreach ($phpFiles as $file) {
            include($file);
        }

        return true;
    }


    /**
     * @param string $namespace
     * @return string
     */
    public function createStubName(string $namespace): string
    {

        $fileName = str_replace('\\', '_', $namespace) . '.php';

        $fileName = mb_convert_case(
            $fileName,
            MB_CASE_LOWER
        );

        return $fileName;
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function removeOldStub(string $namespace): bool
    {
        $file = $this->createStubName($namespace);

        return unlink($this->getPatchinDir() . $file);
    }

    /**
     * @param string $name
     * @param string $namespace
     */
    public function createStub(string $namespace)
    {


        $namespaceTemplate = Template::prepare($this->namespaceStub, ['namespace' => $namespace]);


        $patchingDir = $this->patchinDir;


        $content = $this->stub;
        $stubTemplate = Template::prepare($content, ['name' => $namespace]);

        $content = <<<EOT
$namespaceTemplate
$stubTemplate
EOT;


        $fileName = str_replace('\\', '_', $namespace) . '.php';

        $fileName = mb_convert_case(
            $fileName,
            MB_CASE_LOWER
        );

        if (!is_dir($patchingDir) && !mkdir($patchingDir)) {

            throw new \RuntimeException(sprintf('Directory "%s" was not created', $patchingDir));
        }


        $path = $patchingDir . $fileName;

        file_put_contents($path, $content);

    }
}