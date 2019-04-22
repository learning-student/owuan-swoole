<?php


namespace SwooleTW\Http\Helpers;

/**
 * Class Response
 * @package SwooleTW\Http\Helpers
 * @codeCoverageIgnore
 */
class Response
{


    public  static  function setCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = "",
        string $domain = "",
        bool $secure = false,
        bool $httpOnly = false
    )
    {
        response()->withCookie(
            cookie(
                $name,
                $value,
                $expires,
                $path,
                $domain,
                $secure,
                $httpOnly
            )
        );

        return true;

    }
    /**
     * sets http response code
     *
     * @param int $code
     * @return bool
     */
    public static function httpResponseCode(int $code) : bool
    {
        response()->setStatusCode(
            $code
        );

        return true;
    }

    /**
     * @param string $val
     * @param bool $replace
     * @return bool
     */
    public static function header(string $val, bool $replace = true): bool
    {

        if (strpos($val, ":") === false) {
            return null;
        }


        [$key, $value] = explode(":", $val);


        response()
            ->header($key, $value, $replace);

        return true;
    }

}