<?php

function getImage($file): string{
	// Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
	if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }
	
	// Check $file['error'] value.
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }
	
	// Check filesize.
    //if ($file['size'] > 1000000) {
    //    throw new RuntimeException('Exceeded filesize limit.');
    //}
	
	// DO NOT TRUST $file['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($file['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }
	
	// You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    //if (!move_uploaded_file(
    //    $_FILES['upfile']['tmp_name'],
    //    sprintf('./uploads/%s.%s',
    //        sha1_file($_FILES['upfile']['tmp_name']),
    //        $ext
    //    )
    //)) {
    //    throw new RuntimeException('Failed to move uploaded file.');
    //}
	
	// Read file
	$filename=addslashes($file["tmp_name"]);
	$handle = fopen($filename, "rb");
	$imageFile = addslashes(fread($handle, filesize($filename)));
	fclose($handle);
	
	return $imageFile;
}

function printFormattedException(Exception &$e): void {
	ob_clean();
	$error500 = '
	<font color="red"><b>500 - Internal server error.</b></font>
	';
	echo $error500.'<br><br>';
	
	echo '<tt>';
	echo '<font color="dodgerblue">Exception</font> : '.$e->getMessage()."<br>";
	echo '&nbsp; &nbsp; in <font color="blue">'.$e->getFile().":line <b>".$e->getLine().'</b></font>';
	foreach($e->getTrace() as &$trace) {
		echo "<br>&nbsp; &nbsp; at ";
		if(isset($trace['class'])) {
			echo '<font color="dodgerblue">'.$trace['class'].'</font>';
		}
		if(isset($trace['class']) and isset($trace['function'])) {
			echo "->";
		}
		if(isset($trace['function'])){
			echo $trace['function']."(";
			foreach($trace['args'] as $key => &$arg) {
				$type = getType($arg);
				echo '<font color="dodgerblue">';
				if($type == "string") {
					echo '"'.mb_strimwidth($arg, 0, 200, "...").'"';
				} elseif($type == "array") {
					echo 'Array';
				} elseif($type == "object") {
					echo 'Object('.get_class($arg).')';
				} elseif($type == "boolean"){
					if($arg == true) {
						echo 'true';
					} else {
						echo 'false';
					}
				} elseif($type === "NULL") {
					echo 'NULL';
				} else {
					echo $arg;
				}
				echo '</font>';
				
				if (!($key === array_key_last($trace['args']))) {
					echo ', ';
				}
			}
			echo ")";
		}
		echo ' in <font color="blue">'.$trace['file'].":line <b>".$trace['line'].'</b></font>';
	}
	echo '</tt>';
}

function getImageColorHistogram($image) {
	return 0;
}


$ARR = Array();
function cmd($var) {
	array_push($GLOBALS["ARR"], str_replace(chr(0), '', $var));
}

function cmdPrint():void {
	$temp = 'start powershell "';
	foreach($GLOBALS["ARR"] as &$cmd) {
		if($cmd === null){
			$temp .= "echo 'NULL'; ";
		} else {
			$temp .= "echo '".$cmd."'; ";
		}
	}
	$temp .= 'pause"';
	exec($temp);
}
?>