<?php

/**
 * Loads corresponding app model class if it is called validly.
 * Loads corresponding integrity test class if it is called validly.
 * Loads corresponding dom lib class if it is called validly.
 */
class autoloader
{
    public $i;

    /**
     * @param string $className
     * @throws Exception if invalid q-ty of classes are found
     */
    public static function autoload(string $className)
    {
        $i = 0;


        $fileRelativeName = 'model' . DS . str_replace('_', DS, $className);
        $path = PATH_APP . $fileRelativeName . '.php';
        if (file_exists($path)) {
            if (require_once $path) {
                $i++;
            }
        }

        $fileRelativeName = 'tests' . DS . str_replace('_', DS, $className);
        $path = PATH_QA . $fileRelativeName . '.php';
        if (file_exists($path)) {
            if (require_once $path) {
                $i++;
            }
        }

        $fileRelativeName = str_replace('Laminas' . DS . 'Dom', 'laminas' . DS . 'laminas-dom' . DS . 'src', $className);
        $path = PATH_VENDOR . $fileRelativeName . '.php';
        if (file_exists($path)) {
            if (require_once $path) {
                $i++;
            }
        }


        if ($i !== 1 && $className !== 'PHP_Invoker') {
            $err = err('Class "%s" was not found.', $className);
            $err = prepareIssueCard($err, $path);
//            throw new Exception($err);
        }
    }
}
