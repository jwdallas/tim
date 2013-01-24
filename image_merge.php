<?php

// function declarations

function find_extension($file_info) {
	//return .jpg , .gif , etc
	$extpos = strrpos($file_info, '.');
	if ($extpos === false) { return null; }
	$ext = substr($file_info, $extpos);
	return $ext;
}

function copy_file($src, $dest, $overwrite=true) {
	if (file_exists($dest)) { unlink($dest); }
	if ($src == '') { return true; }

	$ch = curl_init($src);
	$fp = fopen($dest, "wb");

	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);

	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

}

function resize_image($in_name) {
	$info = getimagesize($in_name);
	$orig_width = $info[0];
	$orig_height = $info[1];
	$type = $info[2];

	if ($orig_width == 0 || $orig_height == 0) {
		die("Bad original image size: img $in_name, w=$orig_width h=$orig_height");
	}

	if ($orig_height !== 260) {
		$new_height = 260;
		$new_width = floor($orig_width * ( 260 / $orig_height ));
		
		$img = load_orig_image($in_name, $type);
		$out_img = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($out_img, $img, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
		return $out_img;
		// return save_resized_image($type, $out_img);
	} else {
		return load_orig_image($in_name, $type);
	}
}

function save_resized_image($type, $image) {
	switch ($type) {
		case IMAGETYPE_JPEG:
		case IMAGETYPE_JPEG2000:
			return imagejpeg($image, NULL, 100);
		case IMAGETYPE_PNG:
			return imagepng($image, NULL, 9);
		case IMAGETYPE_GIF:
			return imagegif($image);
		default:
			break;
	}
	die ("Save: type ($type) not recognized.");
}

function load_orig_image($path, $type) {
	switch ($type) {
		case IMAGETYPE_JPEG:
		case IMAGETYPE_JPEG2000:
			return imagecreatefromjpeg($path);
		case IMAGETYPE_PNG:
			return imagecreatefrompng($path);
		case IMAGETYPE_GIF:
			return imagecreatefromgif($path);
		default:
			break;
	}
	die ("Load: type ($type) not recognized.");
}

function create_merged_image($fileA_path, $fileB_path, $fileC_path=false) {
	
	// Get file info
	$info = getimagesize($fileA_path);
	$fileA_width = $info[0];
	$fileA_type = $info[2];
	
	$info = getimagesize($fileB_path);
	$fileB_width = $info[0];
	$fileB_height = $info[1];
	$fileB_type = $info[2];
	
	if ($fileC_path !== false) {
		$info = getimagesize($fileC_path);
		$fileC_width = $info[0];
		$fileC_height = $info[1];
		$fileC_type = $info[2];
	}
	
	// Calculate output width
	if ($fileC_path == false) {
		$fileB_new_width = floor($fileB_width * ( 260 / $fileB_height ));
		$output_width = $fileB_new_width + 30; 
	} else {
		$fileC_new_width = floor($fileC_width * ( 260 / $fileC_height ));
		$output_width = $fileC_new_width + 60;
	}
	
	// Make canvas 
	$output_img = imagecreatetruecolor($output_width, 260);
	
	// Load images
	$shadow_img = load_orig_image('temp_imgs/si_shadow.png', IMAGETYPE_PNG);
	$fileA_img = resize_image($fileA_path);
	$fileB_img = resize_image($fileB_path);
	if ($fileC_path !== false) {
		$fileC_img = resize_image($fileC_path);
	}
	
	// Merge images
	if ($fileC_path == false) {
		imagecopy($output_img, $fileA_img, 0, 0, 0, 0, 29, 260);
		imagecopy($output_img, $shadow_img, 22, 0, 0, 0, 8, 260);
		imagecopy($output_img, $fileB_img, 30, 0, 0, 0, $fileB_width, 260);
	} else {
		imagecopy($output_img, $fileA_img, 0, 0, 0, 0, 29, 260);
		imagecopy($output_img, $shadow_img, 22, 0, 0, 0, 8, 260);		
		imagecopy($output_img, $fileB_img, 30, 0, 0, 0, 29, 260);
		imagecopy($output_img, $shadow_img, 52, 0, 0, 0, 8, 260);		
		imagecopy($output_img, $fileC_img, 60, 0, 0, 0, $fileC_width, 260);
	}
	
	//Output merged image
	$output_path = "temp_imgs/series_image.jpg";
	imagejpeg($output_img, $output_path, 92);

	return $output_path;
}

function upload_to_imgur($file, $api_key) {
	// do some checking on the files
	// get exif data for the image
	$type = exif_imagetype($file);

	if (
		($type == IMAGETYPE_GIF) || 
		($type == IMAGETYPE_PNG) ||
		($type == IMAGETYPE_JPEG)) {

	    $handle = fopen($file, "r");
	    $data = fread($handle, filesize($file));

	    // $data is file data
	    $pvars   = array('image' => base64_encode($data), 'key' => $api_key);
	    $timeout = 30;
	    $curl    = curl_init();
	    $post    = http_build_query($pvars);

	    curl_setopt($curl, CURLOPT_URL, 'http://imgur.com/api/upload.xml');
	    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));

	    $xmlstr = curl_exec($curl);
	    curl_close ($curl);

		if ($xmlstr !== false) {
			$xml = new SimpleXMLElement($xmlstr);
			return array(
				'original_image'	=> (string) $xml->original_image,
				'small_thumbnail'	=> (string) $xml->small_thumbnail,
				'large_thumbnail'	=> (string) $xml->large_thumbnail,
				'imgur_page'		=> (string) $xml->imgur_page,
				'delete_page'		=> (string) $xml->delete_page
			);
		}
		die('Upload failed.');
	}
	die('Invalid image type.');

	// something didn't work out
	return false;		
}


// Run on page call

$number_of_images = 0;
$error = '';

$img1 = strip_tags($_GET['img1']);
$img2 = strip_tags($_GET['img2']);
$img3 = strip_tags($_GET['img3']);

if (!empty($img1)) {
	
	$temp_dir = 'temp_imgs/';
	$ext = find_extension($img1);
	$fileA_path = $temp_dir . 'fileA' . $ext;
	copy_file($img1, $fileA_path, true);
	$ext = find_extension($img2);
	$fileB_path = $temp_dir . 'fileB' . $ext;
	copy_file($img2, $fileB_path, true);
	$ext = find_extension($img3);
	$fileC_path = $temp_dir . 'fileC' . $ext;
	copy_file($img3, $fileC_path, true);
	
	if (!empty($img1) && !empty($img2) && !empty($img3)) {
		$number_of_images = 3;
	}
	if (!empty($img1) && !empty($img2) && empty($img3)) { 
		$number_of_images = 2;
	}
	
	switch($number_of_images) {
		case 2: $new_image_path = create_merged_image($fileA_path, $fileB_path); break;
		case 3: $new_image_path = create_merged_image($fileA_path, $fileB_path, $fileC_path); break;
	}	

  $image = upload_to_imgur($new_image_path,'ba316e9df37b932378114785f946676d');
		if ($image === false) {
			die('Failed to upload image.');
		}
		# HERE!
  echo $image['original_image'];
}
