<?php

class file
{

	function name ($fn)
	{
		$f = substr($fn, strlen($fn) - 1);
		if ($f == '/' || $f == '\\') $fn = substr($fn, 0, strlen($fn) - 1);
		$f_arr = preg_split('/\//', $fn);
		return end($f_arr);
	}

	function nameExtend( $fn, $extend, $direction = 'right' ) {
		$ext = '.'.$this->ext( $fn );
		$dir = dirname( $fn ).'/';
		$name = str_replace( array( $ext, $dir ), '', $fn );
		if ( $direction == 'right' ) $name = $name.$extend;
		else $name = $extend.$name;
		return $dir.$name.$ext;
	}

	function ext ($filename)
	{
		return end(explode('.', $filename));
	}

	function createdir($dn, $adm = 0777)
	{
		if (is_dir($dn)) return true;
		$str = '';
		if (preg_match('/\//', $dn))
		{
			$dn_arr = explode('/', $dn);
			foreach ($dn_arr as $k => $v)
			{
				$str .= $v;
				if (!is_dir($str) && $v != '')
				{
					if (!mkdir($str, $adm))
					{
						//echo $str;exit;
						//if (!mkdir($str))
						//return false;
					}
				}
				$str .= '/';
			}
		}
		return true;
	}

	function upfile ($allow_arr, $tmp_file, $tar_path, $tar_name)
	{
		$ext = strtolower(end(explode('.', $tmp_file['name'])));
		if (in_array($ext, $allow_arr))
		{
			$pic		= $tar_name.'.'.$ext;
			$filename	= str_replace('//', '/', $tar_path.'/'.$pic);
			self::createdir(str_replace(end(explode('/', $filename)), '', $filename));
			if (function_exists('move_uploaded_file'))
			{
				move_uploaded_file($tmp_file['tmp_name'], $filename);
			}
			else
			{
				@copy($tmp_file['tmp_name'], $filename);
			}
		}
		else return false;
		return $pic;
	}

	function createfile ($fn, $str='', $write = true)
	{
		if (!$write && file_exists($fn)) return false;
		if (preg_match('/\//', $fn))
		{
			$filename = end(explode('/', $fn));
			$this->createdir(str_replace('/'.$filename, '', $fn));
		}
		$fp = fopen($fn, 'w');
		fwrite($fp, $str);
		fclose($fp);

		return true;
	}

	function is_empty_dir ($dn)
	{
		return $this->readdir($dn) ? false : true;
	}

	function readdir ($dn)
	{
		if ($hd = opendir($dn))
		{
			while (false !== ($fl = readdir($hd)))
			{
				if ($fl != '.' && $fl != '..')
				{
					$fs[] = $fl;
				}
			}
		}
		return $fs ? $fs : false;
	}

	function readfile ($fn)
	{
		if (file_exists($fn))
		{
			if (function_exists('file_get_contents'))
				return file_get_contents($fn);
			else
			{
				$fp		= fopen($fn, 'r');
				$str	=fread($fp, filesize($fn));
				fclose($fp);
			}
			return $str;
		}
		else
			return null;
	}

	function deletefile ($filepath)
	{
		if (is_array($filepath))
		{
			foreach ($filepath as $i=>$f)
			{
				if (file_exists($f))
				{
					unlink($f);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			if (file_exists($filepath))
			{
				unlink($filepath);
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	function clsdir ($dn)
	{
		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d) is_dir($ds.'/'.$d) ? $this->deldir($ds.'/'.$d) : $this->deletefile($ds.'/'.$d);
					}
				}
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d) is_dir($dn.'/'.$d) ? $this->deldir($dn.'/'.$d) : $this->deletefile($dn.'/'.$d);
				}
			}
		}
	}

	function deldir ($dn, $mod = false)
	{
		//删除目录    在linux下成功，window下删除不了文件夹
		/*
		  fn - 要删除的目录，支持数组
		  mod - 没有权限时是否强制删除
		*/

		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d)
							is_dir($ds.'/'.$d) ? $this->deldir($ds.'/'.$d) : $this->deletefile($ds.'/'.$d);
					}
					if($mod)
						chmod($ds, 0777);
					rmdir($ds);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d)
						is_dir($dn.'/'.$d) ? $this->deldir($dn.'/'.$d) : $this->deletefile($dn.'/'.$d);
				}
				else
				{
					if($mod)
						chmod($dn, 0777);
					rmdir($dn);
				}
			}else
				return false;
		}
	}

	function deleteFiles( $delete_files ) {
		if ( is_array( $delete_files ) ) {
			foreach ( $delete_files as $df ) {
				$this->deleteFiles( $df );
			}
		}
		else {
			@unlink( $delete_files );
		}
	}

	function readDir2( $dirpath ) {
		if ( ! is_dir( $dirpath ) ) {
			return false;
		}

		if ( $hd = opendir( $dirpath ) ) {
			$files = array();
			while ( false !== ( $file = readdir( $hd ) ) ) {
				if ( $file != '.' && $file != '..' ) {
					$files[] = $file;
				}
			}
		}
		else {
			return false;
		}

		closedir( $hd );
		return $files;
	}

	function deleteDirs( $dir_path, $keep_first_dir = false, $level = 0 ) {
		if ( is_array( $dir_path ) ) {
			foreach ( $dir_path as $dp ) {
				$this->deleteDirs( $dp, $keep_first_dir );
			}
		}
		else {
			if ( is_dir( $dir_path ) ) {
				if ( false !== ( $files = $this->readDir2( $dir_path ) ) ) {
					foreach ( $files as $file ) {
						$file = $dir_path.'/'.$file;
						if ( is_dir( $file ) ) {
							$this->deleteDirs( $file, false, $level + 1 );
						}
						else {
							$this->deleteFiles( $file );
						}
					}
					if ( ! $keep_first_dir || $level > 0 ) {
						chmod( $dir_path, 0777 );
						rmdir( $dir_path );
					}
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		return true;
	}

	function copyfile ($fn, $dir = '')
	{
		if ($dir!='' && !is_dir($dir))
			$this->createdir($dir);
		$dir = $dir == '' ? '' : $dir.'/';

		if (is_array($fn))
		{
			foreach ($fn as $f)
				copy($f, $dir.$this->name($f));
		}
		else
		{
			copy($fn, $dir.$this->name($fn));
		}
	}

	function movefile($fn, $odir = '', $dir = '')
	{
		$odir = $odir == '' ? '' : $odir.'/';
		if (!file_exists($odir.$fn)) return false;
		if ($dir != '' && !is_dir($dir)) $this->createdir($dir);
		$dir = $dir == '' ? '' : $dir.'/';

		if (is_array($fn))
		{
			foreach($fn as $f) $this->rnamefile($odir.$f, $dir.$f);
		} else $this->rnamefile($odir.$fn, $dir.$fn);
	}

	function copydir ($dn, $dir, $wrap = true)
	{
		if (is_array($dn))
		{
			foreach ($dn as $ds)
			{
				if (is_dir($ds))
				{
					if ($wrap) $this->createdir($dir.'/'.$this->name($ds));
					if (!$this->is_empty_dir($ds))
					{
						foreach ($this->readdir($ds) as $d) is_dir($ds.'/'.$d) ? $this->copydir($ds.'/'.$d, $dir.'/'.($wrap ? $this->name($ds) : '')) : $this->copyfile($ds.'/'.$d, $dir.'/'.($wrap ? $this->name($ds) : ''));
					}
				} else $this->copyfile($ds, $dir);
			}
		}
		else
		{
			if (is_dir($dn))
			{
				if ($wrap) $this->createdir($dir.'/'.$this->name($dn));
				if (!$this->is_empty_dir($dn))
				{
					foreach ($this->readdir($dn) as $d) is_dir($dn.'/'.$d) ? $this->copydir($dn.'/'.$d, $dir.'/'.($wrap ? $this->name($dn) : '')) : $this->copyfile($dn.'/'.$d, $dir.'/'.($wrap ? $this->name($dn) : ''));
				}
			} else $this->copyfile($dn, $dir);
		}
	}

	function movedir ($dn, $dir)
	{
		$this->copydir($dn, $dir);
		$this->deldir($dn);
	}

	function rnamefile ($fn, $nfn)
	{
		if ($nfn == '' || !file_exists($fn) || file_exists($nfn)) return false;
		rename($fn, $nfn);
		return true;
	}

	function rnamedir ($dn)
	{
		
	}

	function size ($fn)
	{
		//return Byte
		$fjsize = 0;
		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..')
					{
						if (is_dir($fn.'/'.$fl))
							$fjsize += $this->size($fn.'/'.$fl);
						else
						{
							$fjsize += filesize($fn.'/'.$fl);
						}
					}
				}
			}
		}
		else
		{
			$flsize = filesize($fn);
		}
		return $fjsize;
	}

	function info ($fn)
	{
		return array(filectime($fn), filemtime($fn));
	}

	function tree ($fn, $allow_arr = '')
	{
		$list	= array();
		$robots	= array();

		/* robots.txt
		$robots	= explode("\r\n", $this->readfile('../robots.txt'));
		$robots	= array_splice($robots, 1);
		for ($i = 0; $i < count($robots); $i++)
		{
			$robots[$i]	= str_replace('Disallow: ', '', $robots[$i]);
			$tmp		= explode('/', $robots[$i]);
			$robots[$i]	= $tmp[1];
		}
		*/

		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..' && !in_array($fl, $robots))
					{
						if (is_dir($fn.'/'.$fl)) $list[] = $this->tree($fn.'/'.$fl, $allow_arr);
						else
						{
							if (is_array($allow_arr))
							{
								if (in_array($this->ext($fl), $allow_arr)) $list[] = $fn.'/'.$fl;
							} else $list[] = $fn.'/'.$fl;
						}
					}
				}
			}
		} else return false;
		return $list;
	}

	function actionPermissionArray (& $ctlArray, $fn, $startPath = '', $noAllowDir = array())
	{
		//针对后台使用
		if (is_dir($fn))
		{
			if ($fd = opendir($fn))
			{
				while (false !== ($fl = readdir($fd)))
				{
					if ($fl != '.' && $fl != '..' && !in_array($fl, $noAllowDir))
					{
						if (is_dir($fn.'/'.$fl))
						{
							self::actionPermissionArray($ctlArray, $fn.'/'.$fl, $startPath, $noAllowDir);
						}
						else
						{
							if (preg_match('/ctl\.(.*)\.php/', $fn.'/'.$fl))
							{
								$ctl_name = preg_replace('/ctl\.(.*)\.php/', '$1', str_replace($startPath, '', $fn.'/'.$fl));
								if (left($ctl_name, 1) == '/') $ctl_name = left($ctl_name, 1, true);
								$ctlArray[] = array(
									$fn.'/'.$fl,
									//preg_replace('/ctl\.(.*)\.php/', '$1', str_replace($fn.'/', '', $fn.'/'.$fl))
									$ctl_name
								);
							}
						}
					}
				}
			}
		} else return false;
		return $ctlArray;
	}

	/**
	* image_src	原始图片路径
	* save_src	处理后的图片保存路径
	* width		宽度
	* height	高度
	* geometric	等比缩放
	* forcut	为裁剪先进行缩放
	*/
	function resize( $image_src, $save_src, $width, $height, $geometric = true, $forcut = false ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		imagesavealpha( $im, true );
		$old_width = imagesx( $im );
		$old_height = imagesy( $im );

		if ( $geometric ) {
			if ( $old_width / $old_height > $width / $height ) {
				if ( $forcut == true ) {
					$width = intval( $height / $old_height * $old_width );
				}
				else {
					$height = intval( $width / $old_width * $old_height );
				}
			}
			else {
				if ( $forcut == true ) {
					$height = intval( $width / $old_width * $old_height );
				}
				else {
					$width = intval( $height / $old_height * $old_width );
				}
			}
		}

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, 0, 0, $width, $height, $old_width, $old_height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, 0, 0, $width, $height, $old_width, $old_height );
		}

		//ob_start();
		//header('Content-type: image/jpeg');
		/*switch ( $image_state[2] ) {
			case 1 : imagegif( $new, $save_src ); break;
			case 2 : imagejpeg( $new, $save_src, 90 ); break;
			case 3 : imagepng( $new, $save_src ); break;
		}*/
		$this->saveImage( $image_state[2], $new, $save_src );
		//imagejpeg( $new );
		//exit;
	}

	function cutByPos( $image_src, $save_src, $width, $height, $pos = 'center,top' ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		imagesavealpha( $im, true );

		$cx = 0;
		$cy = 0;
		$posarr = explode( ',', $pos );
		if ( count( $posarr ) != 2 ) {
			$posarr = array( 'center', 'top' );
		}
		$old_width = imagesx( $im );
		$old_height = imagesy( $im );

		if ( $width >= $old_width && $height >= $old_height ) {
			$this->saveImage( $image_state[2], $im, $save_src );
			return false;
		}

		if ( $width >= $old_width ) {
			$width = $old_width;
		}
		else {
			switch ( $posarr[0] ) {
				case 'left' : $cx = 0; break;
				case 'center' : $cx = ( $old_width - $width ) / 2; break;
				case 'right' : $cx = $old_width - $width; break;
			}
		}
		if ( $height >= $old_height ) {
			$height = $old_height;
		}
		else {
			switch ( $posarr[1] ) {
				case 'top' : $cy = 0; break;
				case 'middle' : $cy = ( $old_height - $height ) / 2; break;
				case 'bottom' : $cy = $old_height - $height; break;
			}
		}

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, $cx, $cy, $width, $height, $width, $height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, $cx, $cy, $width, $height, $width, $height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	function cutByPosBound( $image_src, $save_src, $bounds ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		$old_width = $image_state[0];
		$old_height = $image_state[1];
		imagesavealpha( $im, true );

		$width = $bounds['x2'] - $bounds['x1'];
		$height = $bounds['y2'] - $bounds['y1'];
		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}

		//透明
		if ( $image_state[2] == 1 || $image_state[2] == 3 ) {
			$transparent_index = imagecolortransparent( $im );
			if ( $transparent_index >= 0 ) {
				$tp_color = imagecolorsforindex( $im, $transparent_index );
				$transparent_index = imagecolorallocate( $new, $tp_color['red'], $tp_color['green'], $tp_color['blue'] );
				imagefill( $new, 0, 0, $transparent_index );
				imagecolortransparent( $new, $transparent_index );
			}
			else if ( $image_state[2] == 3 ) {
				imagealphablending( $new, false );
				$color = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
				imagefill( $new, 0, 0, $color );
				imagesavealpha( $new, true );
			}
		}

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, 0, 0, $bounds['x1'], $bounds['y1'], $width, $height, $width, $height );
		}
		else {
			imagecopyresized( $new, $im, 0, 0, $bounds['x1'], $bounds['y1'], $width, $height, $width, $height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	function fillColor( $image_src, $save_src, $width, $height, $color ) {
		$image_state = getimagesize( $image_src );
		switch ( $image_state[2] ) {
			case 1 : $im = imagecreatefromgif( $image_src ); break;
			case 2 : $im = imagecreatefromjpeg( $image_src ); break;
			case 3 : $im = imagecreatefrompng( $image_src ); break;
		}
		$old_width = $image_state[0];
		$old_height = $image_state[1];
		$x1 = $y1 = 0;
		$x1 = abs( $old_width - $width ) / 2;
		$y1 = abs( $old_height - $height ) / 2;
		imagesavealpha( $im, true );

		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$new = imagecreatetruecolor( $width, $height );
		}
		else {
			$new = imagecreate( $width, $height );
		}
		$bg = imagecolorallocate( $new, $color[0], $color[1], $color[2] );
		imagefill( $new, 0, 0, $bg );

		if ( function_exists( 'imagecopyresampled' ) ) {
			imagecopyresampled( $new, $im, $x1, $y1, 0, 0, $old_width, $old_height, $old_width, $old_height );
		}
		else {
			imagecopyresized( $new, $im, $x1, $y1, 0, 0, $old_width, $old_height, $old_width, $old_height );
		}

		$this->saveImage( $image_state[2], $new, $save_src );
	}

	function saveImage( $image_state, $im, $save_src ) {
		switch ( $image_state ) {
			case 1 : imagegif( $im, $save_src ); break;
			case 2 : imagejpeg( $im, $save_src, 90 ); break;
			case 3 : imagepng( $im, $save_src ); break;
		}
	}

}

?>