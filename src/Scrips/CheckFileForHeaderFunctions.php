<?php

namespace SwooleTW\Http\Scrips;

use PhpParser\{Node, NodeFinder, ParserFactory};

/**
 * Class CheckFileForHeaderFunctions
 */
class CheckFileForHeaderFunctions
{

    /**
     * @param string $content
     * @return Node\Stmt[]|null
     */
    public function parsePhpContent(string $content): ?array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        return $parser->parse($content);

    }

    /**
     * @param Node|Node[] $asts
     * @return string
     */
    public function getNamespace($asts) : string
    {
        $finder = new NodeFinder();


        $namespace = $finder->findFirstInstanceOf($asts, Node\Stmt\Namespace_::class);

        /**
         * @var Node\Stmt\Namespace_ $namespace
         *
         */

        return $namespace->name->toString();
    }

    /**
     * @var array
     */
    private static $functionNames = [
        'header',
        'setcookie',
        'http_response_code'
    ];


    /**
     * @param string $name
     * @param string $content
     * @return bool
     */
    private function checkForFunctionExists(string $name, string $content): bool
    {
        return strpos(
                $content,
                "$name("
            ) !== false || strpos($content, "$name (") !== false;
    }


    /**
     * @param string $content
     * @return bool
     */
    private function checkFunctions(string $content)
    {
        foreach (static::$functionNames as $functionName) {
            if ($this->checkForFunctionExists($functionName, $content) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filename
     * @return array|bool
     */
    public function check(string $filename)
    {
        if (!file_exists($filename)) {
            return false;
        }

        $content = file_get_contents($filename);


        if ($this->checkFunctions($content) === false) {

            return false;
        }

        $ast = $this->parsePhpContent($content);

        $nodeFinder = new NodeFinder;

        $nodeFinder->findInstanceOf($ast, Node\Expr\FuncCall::class);

        $findedFunctions = $nodeFinder->find($ast, function (Node $node) {
            if (!$node instanceof Node\Expr\FuncCall) {
                return false;
            }


            if (!method_exists($node->name, "getFirst")) {
                return false;
            }

            $name = $node->name->getFirst();

            return in_array($name, static::$functionNames, true);

        });


        if (count($findedFunctions) === 0) {
            return false;
        }

        $namespace = $this->getNamespace($ast);

        $find = array_map(function (Node\Expr\FuncCall $call) {
            return $call->name->toLowerString();
        }, $findedFunctions);


        return [
            $find,
            $namespace
        ];
    }
}