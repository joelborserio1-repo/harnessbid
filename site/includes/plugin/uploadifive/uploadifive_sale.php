<?php

/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2010 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

session_start();
include("../../loader.php");
		
$uploadDir = FILE_rel.'temp/'.session_id().'/sale/';
@mkdir(FILE_path.'temp/'.session_id().'/');
@mkdir(FILE_path.'temp/'.session_id().'/sale/');

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$raw_data = explode(".",$_FILES['Filedata']['name']);
	$extpos = count($raw_data)-1;
	$ext = $raw_data[$extpos];
	unset($raw_data[$extpos]);
	$raw_name = implode(".",$raw_data);
	$filename = $raw_name."-".rand(1,999).".".$ext;
	$targetFile = $uploadDir . $filename;

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);

		// Save the file
		move_uploaded_file($tempFile, $targetFile);
		chmod($targetFile,0777);
		
		echo 1;

}

?>