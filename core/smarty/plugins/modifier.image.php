<?php

/**
 Image adapter
 @Author: Curitis Niewei
 
 @param: method -> Adapter method (cut, fill)
 @return: New Image Url
 */


function smarty_modifier_image( $string, $width, $height, $method = 'fill',$baseOnSkinPath=false) {

	$baseDir = $baseOnSkinPath?TPL_DIR:UPDATE_DIR;

	$noImage = false;
	if ( empty( $string ) || ! file_exists( $baseDir.$string ) || ! in_array( $method, array( 'cut', 'fill' ) ) ) {
		$noImage = true;
		$string = 'no-image.gif';
		if ( ! file_exists( $baseDir.$string ) ) {
			return '';
		}
		$method = 'cut';
		//return $string;
	}

	$width = (int)$width;
	$height = (int)$height;

	if ( $width <= 0 || $height <= 0 ) {
		return $string;
	}

	$image_state = getimagesize( $baseDir.$string );
	
	switch ( $image_state[2] ) {
		case 1 : $im = imagecreatefromgif( $baseDir.$string ); break;
		case 2 : $im = imagecreatefromjpeg( $baseDir.$string ); break;
		case 3 : $im = imagecreatefrompng( $baseDir.$string ); break;
	}
	$old_width = $image_state[0];
	$old_height = $image_state[1];

	if ( $old_width == $width && $old_height == $height ) {
		return $string;
	}

	$file = new file;
	$newImageDir = $baseDir.'thumbnails/';
	$newImageUrl = $file->nameExtend( $string, "_{$width}x{$height}_{$method}" );

	if ( file_exists( $newImageDir.$newImageUrl ) ) {
		return 'thumbnails/'.$newImageUrl;
	}

	$newImagePath = $file->name( $newImageUrl );
	$newImagePath = str_replace( $newImagePath, '', $newImageUrl );
	$file->createdir( $newImageDir.$newImagePath, 0777 );
	if ( $method == 'fill' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height );
		$file->fillColor( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height, array( 255, 255, 255 ) );
	}
	elseif ( $method == 'cut' ) {
		$file->resize( $baseDir.$string, $newImageDir.$newImageUrl, $width, $height, true, true );
		$file->cutByPos( $newImageDir.$newImageUrl, $newImageDir.$newImageUrl, $width, $height );
	}

	// watermarkImage($newImageDir.$newImageUrl); disable water mark of image

	return 'thumbnails/'.$newImageUrl;
}

?>