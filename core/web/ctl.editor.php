<?php

class ctl_editor extends cmsPage
{

	public function pic_action () #act_name = 图片上传#
	{
		//这是可以加上会员是否登录的判断
		//if ( !$this->loginUser ) { echo '请登录'; exit; }

		$fn		= get2('CKEditorFuncNum');
		$exts	= array('jpg', 'jpeg', 'png', 'gif');

		if (is_post() && $_FILES)
		{
			$filepath	= date('Y-m');

			$this->file->createdir('data/upload/'.$filepath);
			$filename = $this->file->upfile($exts, $_FILES['upload'], UPDATE_DIR, $filepath.'/'.date('YmdHis').$this->createRnd());
			if ($filename)
			{
				echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$fn.', \''.UPLOAD_PATH.$filename.'\', \'\');</script>';
				exit;
			}
			else die('<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$fn.', \'\', \'上传图片失败\');</script>');
		}
	}

}

?>