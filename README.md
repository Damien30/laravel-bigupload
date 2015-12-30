# laravel-bigupload
Use Laravel with the plugin bigupload

This repo use https://github.com/sthielen/BigUpload, you must import JS/CSS in your application.

1. [Features](#features)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Options](#options)

----

<a id="features"></a>
## Features
- Upload large files over the server configuration 
- ProgressBar with time remaining
- Button upload/resume/Cancel

<a id="installation"></a>
## Installation

In your project base directory run

	composer require "dlouvard/laravel-bigupload":"master@dev"
	
To bring up the config file run, if you want to customize

	php artisan vendor:publish
	
Then edit `config/app.php` and add the service provider within the `providers` array.

	'providers' => array(
		...
		Dlouvard\LaravelBigupload\BiguploadServiceProvider::class,

<a id="usage"></a>
## Usage

- Import in your controller or layout bigupload.js and bigupload.css
- In your file.blade.php install the block bigupload with the javascript
- Prepare you ajax file with "Try{...}" and the response in JSON

<a id="options"></a>
## Options

I propose a customization file for the bigupload.js send cancel function



