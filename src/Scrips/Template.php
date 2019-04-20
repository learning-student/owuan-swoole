<?php


namespace SwooleTW\Http\Scrips;


class Template
{

    /**
     * @param string $content
     * @param array $params
     * @return string
     */
    public static function prepare(string $content, array $params = [] ) : string
    {
        foreach ($params as $param => $value){
            $content = str_replace("{{$param}}", $value, $content);
        }


        return $content;
    }

}