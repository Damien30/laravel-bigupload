<?php namespace Dlouvard\LaravelBigupload;

/**
 * Created by PhpStorm.
 * User: dlouvard
 * Date: 26/12/2015
 * Time: 12:07
 */
ini_set('max_input_vars', 20000);
use Illuminate\Foundation\Application;

class BigUpload
{
    /**
     * Temporary directory for uploading files
     */
    const TEMP_DIRECTORY = '../files/tmp/';

    /**
     * Directory files will be moved to after the upload is completed
     */
    const MAIN_DIRECTORY = '../files/';

    /**
     * Max allowed filesize. This is for unsupported browsers and
     * as an additional security check in case someone bypasses the js filesize check.
     *
     * This must match the value specified in main.js
     */
    const MAX_SIZE = 2147483648;

    /**
     * Temporary directory
     * @var string
     */
    private $tempDirectory;

    /**
     * Directory for completed uploads
     * @var string
     */
    private $mainDirectory;

    /**
     * Name of the temporary file. Used as a reference to make sure chunks get written to the right file.
     * @var string
     */
    private $tempName;

    /**
     * Constructor function, sets the temporary directory and main directory
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->setTempDirectory(config('bigupload.TEMP_DIRECTORY'));
        $this->setMainDirectory(config('bigupload.MAIN_DIRECTORY'));
    }

    /**
     * Create a random file name for the file to use as it's being uploaded
     * @param string $value Temporary filename
     */
    public function setTempName($value = null) {
        if ($value) {
            $this->tempName = sanitizeFileName($value);
        }
        else {
            $this->tempName = mt_rand() . '.tmp';
        }
    }

    /**
     * Return the name of the temporary file
     * @return string Temporary filename
     */
    public function getTempName() {
        return $this->tempName;
    }

    /**
     * Set the name of the temporary directory
     * @param string $value Temporary directory
     */
    public function setTempDirectory($value) {
        $this->tempDirectory = $value;
        return true;
    }

    /**
     * Return the name of the temporary directory
     * @return string Temporary directory
     */
    public function getTempDirectory() {
        return $this->tempDirectory;
    }

    /**
     * Set the name of the main directory
     * @param string $value Main directory
     */
    public function setMainDirectory($value) {
        $this->mainDirectory = $value;
    }

    /**
     * Return the name of the main directory
     * @return string Main directory
     */
    public function getMainDirectory() {
        return $this->mainDirectory;
    }

    /**
     * Function to upload the individual file chunks
     * @return string JSON object with result of upload
     */
    public function uploadFile() {
        // Make sure the total file we're writing to hasn't surpassed the file size limit
        $tmpPath = $this->getTempDirectory() . $this->getTempName();
        if (@file_exists($tmpPath)) {
            $fsize = @filesize($tmpPath);

            if ($fsize === false) {
                return array(
                    'errorStatus' => 553,
                    'errorText' => 'File part access error.'
                );
            }
            if ($fsize > config('bigupload.MAX_SIZE')) {
                $this->abortUpload();
                return array(
                    'errorStatus' => 413,
                    'errorText' => 'File is too large.'
                );
            }
        }

        // Open the raw POST data from php://input
        $fileData = @file_get_contents('php://input');
        if ($fileData === false) {
            return array(
                'errorStatus' => 552,
                'errorText' => 'File part upload error.'
            );
        }

        // Write the actual chunk to the larger file
        $handle = @fopen($tmpPath, 'a');
        if ($handle === false) {
            return array(
                'errorStatus' => 553,
                'errorText' => 'File part access error.'
            );
        }

        $rv = @fwrite($handle, $fileData);
        @fclose($handle);
        if ($rv === false) {
            return array(
                'errorStatus' => 554,
                'errorText' => 'File part write error.'
            );
        }

        return array(
            'key' => $this->getTempName(),
            'errorStatus' => 0
        );
    }

    /**
     * Function for cancelling uploads while they're in-progress; deletes the temp file
     * @return string JSON object with result of deletion
     */
    public function abortUpload() {
        if (@unlink($this->getTempDirectory() . $this->getTempName())) {
            return array(
                'errorStatus' => 0
            );
        }
        else {
            return array(
                'errorStatus' => 405,
                'errorText' => 'Unable to delete temporary file.'
            );
        }
    }

    /**
     * Function to rename and move the finished file
     * @param  string $final_name Name to rename the finished upload to
     * @return string JSON object with result of rename
     */
    public function finishUpload($finalName) {
        $dstName = sanitizeFileName($finalName);
        $dstPath = $this->getMainDirectory() . $dstName;
        if (@rename($this->getTempDirectory() . $this->getTempName(), $dstPath)) {
            return array(
                'errorStatus' => 0,
                'fileName' => $dstName
            );
        }
        else {
            return array(
                'errorStatus' => 405,
                'errorText' => 'Unable to move file to "' . $dstPath . '" after uploading.'
            );
        }
    }

    /**
     * Basic php file upload function, used for unsupported browsers.
     * The output on success/failure is very basic, and it would be best to have these errors return the user to index.html
     * with the errors printed on the form, but that is beyond the scope of this project as it is very application specific.
     * @return string Success or failure of upload
     */
    public function postUnsupported($files) {
        if (empty($files)) {
            $files = $_FILES['bigUploadFile'];
        }
        if (empty($files)) {
            return array(
                'errorStatus' => 550,
                'errorText' => 'No BigUpload file uploads were specified.'
            );
        }
        $name = sanitizeFileName($files['name']);
        $size = $files['size'];
        $tempName = $files['tmp_name'];

        $fsize = @filesize($tempName);
        if ($fsize === false) {
            return array(
                'errorStatus' => 553,
                'errorText' => 'File part access error.'
            );
        }
        if ($fsize > config('bigupload.MAX_SIZE')) {
            return array(
                'errorStatus' => 413,
                'errorText' => 'File is too large.'
            );
        }

        $dstPath = $this->getMainDirectory() . $name;
        if (@move_uploaded_file($tempName, $dstPath)) {
            return array(
                'errorStatus' => 0,
                'fileName' => $dstName,
                'errorText' => 'File uploaded.'
            );
        }
        else {
            return array(
                'errorStatus' => 405,
                'errorText' => 'There was an error uploading the file to "' . $dstPath . '".'
            );
        }
    }
}