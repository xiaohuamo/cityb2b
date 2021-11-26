 <?php
require 'vendor/autoload.php';
use Intervention\Image\ImageManager;


class Position {
	const TOPLEFT ='top-left';
	const TOP ='top';
	const TOPRIGHT ='top-right';
	const LEFT ='left';
	const CENTER ='center';
	const RIGHT ='right';
	const BOTTOMLEFT ='bottom-left';
	const BOTTOM ='bottom';
	const BOTTOMRIGHT ='bottom-right';
}

class mdl_imageprocess extends mdl_base
{   

	// protected $tableName = '#@_article';
	private $manager;

	const BASEPATH = "themes/zh-cn/images/poster/";

	const TEMPLATEPATH = "themes/zh-cn/images/poster/template/";

	const OUTPUTPATH = "themes/zh-cn/images/poster/poster/";

	const FONTFILE = DOC_DIR."fonts/arial-bold.otf";

	private $basePath,$templatePath,$outputPath,$fontDefinition ;

	function __construct()
	{
		$this->manager = new ImageManager(array('driver' => 'GD'));

		$this->basePath = DOC_DIR . self::BASEPATH;

		$this->templatePath = DOC_DIR . self::TEMPLATEPATH;

		$this->outputPath = DOC_DIR . self::OUTPUTPATH;

		$this->fontDefinition = function($font) {
			$font->file(self::FONTFILE);
		    $font->size(24);
		    $font->color('#fdf6e3');
		    $font->align('center');
		    $font->valign('top');
		    $font->angle(0);
		};
	}

	public function loadTemplate()
	{	
		$fileList = [];

		$dir = $this->templatePath;

		if (is_dir($dir)) {
		    if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
		            $info = pathinfo($file);
		            if($info['extension'] === 'jpg') {
		            	$fileList[] = $file;
		            }
		        }
		        closedir($dh);
		    }
		}

		return $fileList;
	}

	public function generatePoster($template, $insertImages,$insertText,$userId)
	{	
		$nameIdentifier = "";

		$template = $this->manager->make($this->templatePath.$template);
		$templateWidth = $template->width();
		$templateHeight = $template->height();

		$nameIdentifier.=$template;

		if (sizeof($insertImages) > 0) {
			foreach ($insertImages as $insertImage) {
				$img =  $this->manager->make($insertImage['path'])->resize(100, 100);
				$template->insert($img,$insertImage['position']);
				$nameIdentifier.=$insertImage['path'];
			}
		}

		if ($insertText) {
			$template->text($insertText ,$templateWidth/2, $templateHeight/2, $this->fontDefinition);
			$nameIdentifier.= $insertText;
		}

		$fileName = sha1($nameIdentifier) . ".jpg";
		$folder = $userId."/";

		$savePath = $this->outputPath.$folder;
		if (!is_dir($savePath))mkdir($savePath);
		$template->save($savePath.$fileName);

		return HTTP_ROOT_WWW . self::OUTPUTPATH .$folder. $fileName;

	}

	public function test()
	{	
		$img1 = $this->manager->make($this->basePath.'test.jpg')->resize(32, 24)->save($this->basePath.'test_32_24.jpg');

		$img2 = $this->manager->make($this->basePath.'test.jpg')->resize(1000, 2000)->save($this->basePath.'test_1000_2000.jpg');

		$img3 = $this->manager->make($this->basePath.'test.jpg')->resize(640, 480)->save($this->basePath.'test_640_480.jpg');

		$this->manager->make($this->basePath.'test.jpg')->insert($this->basePath.'test_32_24.jpg')->save($this->basePath.'test_insert.jpg');

		// use callback to define details
		$this->manager->make($this->basePath.'test.jpg')->text('The quick brown fox jumps over the lazy dog.',0, 0, $this->fontDefinition)->save($this->basePath.'test_text.jpg');
	}
}

?>