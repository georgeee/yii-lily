<?php

/**
 * LTranslateManager class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LTranslateManager is a console command class, which is used to extract/renew strings, that are translated using LilyModule::t
 *
 * @package application.commands
 */
class LTranslateManager extends CConsoleCommand {

    public function actionExtract() {
        $files = array();
        $fpath = Yii::getPathOfAlias('lily');
        $this->recurse($fpath, $files);
        $patternQ = '~LilyModule\\s*\\:\\:\\s*t\\s*\\(\\s*\'((?:[^\'\\\\]|(?:\\\\.))*)\'~ms';
        $patternD = '~LilyModule\\s*\\:\\:\\s*t\\s*\\(\\s*"((?:[^"\\\\]|(?:\\\\.))*)"~ms';
        $template_path = $fpath . '/messages/template/default.php';
        $ru_path = $fpath . '/messages/ru/default.php';
        $_ru_tr = include($ru_path);
        $ru_tr = array();
        $t_tr = array();
        foreach ($files as $file) {
            preg_match_all($patternQ, file_get_contents($fpath . $file), $matchesQ, PREG_SET_ORDER);
            preg_match_all($patternD, file_get_contents($fpath . $file), $matchesD, PREG_SET_ORDER);
            echo "=========================\nFile $file\n\n";
            foreach ($matchesD as $match) {
                $phrase = eval('return "' . $match[1] . '";');
                if (empty($_ru_tr[$phrase]))
                    $ru_tr[$phrase] = $phrase;
                else
                    $ru_tr[$phrase] = $_ru_tr[$phrase];
                $t_tr[$phrase] = '';
            }
            foreach ($matchesQ as $match) {
                $phrase = eval('return \'' . $match[1] . '\';');
                if (empty($_ru_tr[$phrase]))
                    $ru_tr[$phrase] = $phrase;
                else
                    $ru_tr[$phrase] = $_ru_tr[$phrase];
                $t_tr[$phrase] = '';
            }
        }
        file_put_contents($template_path, '<?php return ' . var_export($t_tr, 1) . ';');
        file_put_contents($ru_path, '<?php return ' . var_export($ru_tr, 1) . ';');
    }

    function recurse($dir, &$result, $prefix = '') {
        if (is_dir($dir . $prefix)) {
            $objects = scandir($dir . $prefix);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . $prefix . "/" . $object) == "dir") {
                        $this->recurse($dir, $result, $prefix . "/" . $object);
                    } else if (preg_match('~\.php$~i', $object)) {
                        $result [] = $prefix . "/" . $object;
                    }
                }
            }
        }
    }

}

?>
