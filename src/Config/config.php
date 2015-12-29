<?php
/**
 * Created by PhpStorm.
 * User: dlouvard
 * Date: 26/12/2015
 * Time: 12:03
 */
return [
    /**
     * Temporary directory for uploading files
     */
    'TEMP_DIRECTORY' => storage_path('files/tmp'),
    /**
     * Directory files will be moved to after the upload is completed
     */
    'MAIN_DIRECTORY' => storage_path('files'),
    /**
     * Max allowed filesize. This is for unsupported browsers and
     * as an additional security check in case someone bypasses the js filesize check.
     *
     * This must match the value specified in main.js
     */
    'MAX_SIZE' => '2147483648'
];