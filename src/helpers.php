<?php
/**
 * Created by PhpStorm.
 * User: dlouvard
 * Date: 26/12/2015
 * Time: 11:59
 */
use Illuminate\Support\Facades\App;
use Stolz\Assets;
if (!function_exists('sanitizeFileName')) {
    function sanitizeFileName($filename)
    {
        // Remove special accented characters - ie. sí.
        $clean_name = strtr($filename, array('Š' => 'S', 'Ž' => 'Z', 'š' => 's', 'ž' => 'z', 'Ÿ' => 'Y', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y'));
        $clean_name = strtr($clean_name, array('Þ' => 'TH', 'þ' => 'th', 'Ð' => 'DH', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae', 'µ' => 'u'));

        // Enforce ASCII-only & no special characters
        $clean_name = preg_replace(array('/\s+/', '/[^a-zA-Z0-9_\.\-]/'), array('.', ''), $clean_name);
        $clean_name = preg_replace(array('/--+/', '/__+/', '/\.\.+/'), array('-', '_', '.'), $clean_name);
        $clean_name = trim($clean_name, '-_.');

        // Some file systems are case-sensitive (e.g. EXT4), some are not (e.g. NTFS).
        // We simply assume the latter to prevent confusion later.
        //
        // Note 1: camelCased file names are converted to dotted all-lowercase: `camel.case`
        // Note 2: we assume all file systems can handle filenames with multiple dots
        //         (after all only vintage file systems cannot, e.g. VMS/RMS, FAT/MSDOS)
        $clean_name = preg_replace('/([a-z])([A-Z]+)/', '$1.$2', $clean_name);
        $clean_name = strtolower($clean_name);

        // And for operating systems which don't like large paths / filenames, clip the filename to the last 64 characters:
        $clean_name = substr($clean_name, -64);
        $clean_name = ltrim($clean_name, '-_.');
        return $clean_name;
    }
}
if (!function_exists('main')) {
    function main($action, $tempName, $finalFileName, $files)
    {
        // Instantiate the class
        //$bigUpload = new BigUpload;
        $bigUpload = App::make('bigupload');

        $bigUpload->setTempName($tempName);

        switch ($action) {
            case 'upload':
                return $bigUpload->uploadFile();

            case 'abort':
                return $bigUpload->abortUpload();

            case 'finish':
                return $bigUpload->finishUpload($finalFileName);

            case 'post-unsupported':
            case 'vanilla':
                return $bigUpload->postUnsupported($files);

            case 'help':
                return array(
                    'errorStatus' => 552,
                    'errorText' => "You've reached the BigUpload gateway. Machines will know what to do."
                );

            default:
                return array(
                    'errorStatus' => 550,
                    'errorText' => 'Unknown action. Internal failure.'
                );
        }
    }
}