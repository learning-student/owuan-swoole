<?php


namespace SwooleTW\Http\Scrips;

use Composer\Installer\PackageEvent;


class ComposerScripts
{

    /**
     * @param PackageEvent $event
     */
    public static function preUninstall(PackageEvent $event): void
    {
        $allPhp = static::getAllPhpFiles(
            static::findOriginDir($event)
        );


        $patching = new MonkeyPatching();

        $files = glob(sprintf(
            "%s*.php", $patching->getPatchinDir()
        ), GLOB_BRACE);

        $patchinPhpFiles = array_map(function ($name) {
            return basename($name, ".php");
        }, $files);


        $check = new CheckFileForHeaderFunctions();
        foreach ($allPhp as $file) {

            $content = file_get_contents($file);
            $parse = $check->parsePhpContent($content);

            // get namespace
            $namespace = mb_convert_case(
                $check->getNamespace($parse),
                MB_CASE_LOWER
            );


            // turn it into file format
            $namespace = str_replace('\\', '_', $namespace);

            $search = array_search($namespace, $patchinPhpFiles, true);
            if ($search !== false) {

                $path = $patching->getPatchinDir();

                $path .= $patchinPhpFiles[$search] . '.php';

                try {
                    // remove file
                    unlink($path);
                } catch (\Exception $exception) {
                    // do nothing
                }

            }

        }

    }

    /**
     * @param PackageEvent $event
     * @return string
     */
    private static function findOriginDir(PackageEvent $event): string
    {
        $package = $event->getOperation()->getPackage();
        $installationManager = $event->getComposer()->getInstallationManager();
        return $installationManager->getInstallPath($package);
    }


    /**
     * @param string $originDir
     * @return array
     */
    private static function getAllPhpFiles(string $originDir): array
    {
        $allPhp = [];


        // run through installed package dir and find php files
        $directory = new \RecursiveDirectoryIterator($originDir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regerx = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);


        foreach ($regerx as $files) {
            foreach ($files as $checkFile) {
                $allPhp[] = $checkFile;
            }
        }

        return $allPhp;
    }

    /**
     * Handle the post-update Composer event.
     *
     * @param \Composer\Script\Event $event
     * @return bool
     */
    public static function postUpdate(PackageEvent $event): bool
    {

        try {
            $originDir = static::findOriginDir($event);

            // collect all php files
            $allPhpFiles = static::getAllPhpFiles($originDir);


            // initialize checker
            $checker = new CheckFileForHeaderFunctions();

            $patching = new MonkeyPatching();

            foreach ($allPhpFiles as $checkFile) {
                // check file
                $response = $checker->check($checkFile);

                // if we couldn't find any header function just move on
                if ($response === false) {

                    continue;
                }

                [$find, $namespace] = $response;

                /**
                 * @var array $find
                 * @var string $namespace
                 */

                $patching->createStub($namespace);

            }


            return true;
        } catch (\Exception $exception) {
            // do nothing else
            return false;
        }


    }

}