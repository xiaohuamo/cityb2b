<?php

class createZip{

	public $compressedData=array();
	public $centralDirectory=array();
	public $endOfCentralDirectory="\x50\x4b\x05\x06\x00\x00\x00\x00";
	public $oldOffset=0;
	
	public function addDirectory($directoryName){
		$directoryName=str_replace("\\", "/", $directoryName);
		
		$feedArrayRow="\x50\x4b\x03\x04";
		$feedArrayRow.="\x0a\x00";
		$feedArrayRow.="\x00\x00";
		$feedArrayRow.="\x00\x00";
		$feedArrayRow.="\x00\x00\x00\x00";

		$feedArrayRow.=pack("V",0);
		$feedArrayRow.=pack("V",0);
		$feedArrayRow.=pack("V",0);
		$feedArrayRow.=pack("v",strlen($directoryName));
		$feedArrayRow.=pack("v",0);
		$feedArrayRow.=$directoryName;
		
		$feedArrayRow.=pack("V",0);
		$feedArrayRow.=pack("V",0);
		$feedArrayRow.=pack("V",0);
		
		$this->compressedData[]=$feedArrayRow;
		
		$newOffset=strlen(implode("",$this->compressedData));
		
		$addCentralRecord="\x50\x4b\x01\x02";
		$addCentralRecord.="\x00\x00";
		$addCentralRecord.="\x0a\x00";
		$addCentralRecord.="\x00\x00";
		$addCentralRecord.="\x00\x00";
		$addCentralRecord.="\x00\x00\x00\x00";
		$addCentralRecord.=pack("V",0);
		$addCentralRecord.=pack("V",0);
		$addCentralRecord.=pack("V",0);
		$addCentralRecord.=pack("v",strlen($directoryName));
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$ext="\x00\x00\x10\x00";
		$ext="\xff\xff\xff\xff";
		$addCentralRecord.=pack("V",16);
		
		$addCentralRecord.=pack("V",$this->oldOffset);
		$this->oldOffset=$newOffset;
		
		$addCentralRecord.=$directoryName;
		
		$this->centralDirectory[]=$addCentralRecord;
	}

	public function addFile($data,$directoryName){
		$directoryName=str_replace("\\","/",$directoryName);
		
		$feedArrayRow="\x50\x4b\x03\x04";
		$feedArrayRow.="\x14\x00";
		$feedArrayRow.="\x00\x00";
		$feedArrayRow.="\x08\x00";
		$feedArrayRow.="\x00\x00\x00\x00";

		$uncompressedLength=strlen($data);
		$compression=crc32($data);
		$gzCompressedData=gzcompress($data);
		$gzCompressedData=substr(substr($gzCompressedData,0,strlen($gzCompressedData)-4),2);
		$compressedLength=strlen($gzCompressedData);
		$feedArrayRow.=pack("V",$compression);
		$feedArrayRow.=pack("V",$compressedLength);
		$feedArrayRow.=pack("V",$uncompressedLength);
		$feedArrayRow.=pack("v",strlen($directoryName));
		$feedArrayRow.=pack("v",0);
		$feedArrayRow.=$directoryName;
		
		$feedArrayRow.=$gzCompressedData;

		$feedArrayRow.=pack("V",$compression);
		$feedArrayRow.=pack("V",$compressedLength);
		$feedArrayRow.=pack("V",$uncompressedLength);
		
		$this->compressedData[]=$feedArrayRow;
		
		$newOffset=strlen(implode("",$this->compressedData));
		
		$addCentralRecord="\x50\x4b\x01\x02";
		$addCentralRecord.="\x00\x00";
		$addCentralRecord.="\x14\x00";
		$addCentralRecord.="\x00\x00";
		$addCentralRecord.="\x08\x00";
		$addCentralRecord.="\x00\x00\x00\x00";
		$addCentralRecord.=pack("V",$compression);
		$addCentralRecord.=pack("V",$compressedLength);
		$addCentralRecord.=pack("V",$uncompressedLength);
		$addCentralRecord.=pack("v",strlen($directoryName));
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("v",0);
		$addCentralRecord.=pack("V",32);
		
		$addCentralRecord.=pack("V",$this->oldOffset);
		$this->oldOffset=$newOffset;
		
		$addCentralRecord.=$directoryName;
		
		$this->centralDirectory[]=$addCentralRecord;
	}

	public function getZippedfile(){
		$data=implode("",$this->compressedData);
		$controlDirectory=implode("",$this->centralDirectory);
		
		return $data.
		$controlDirectory.
		$this -> endOfCentralDirectory.
		pack("v",sizeof($this->centralDirectory)).
		pack("v",sizeof($this->centralDirectory)).
		pack("V",strlen($controlDirectory)).
		pack("V",strlen($data)).
		"\x00\x00";
	}

	public function forceDownload($archiveName){
		$headerInfo='';
		
		if(ini_get2('zlib.output_compression'))
			ini_set('zlib.output_compression','Off');
		
		if($archiveName==""){
			echo "ERROR:The download file was NOT SPECIFIED.";
			exit;
		}elseif (!file_exists($archiveName)){
			echo "ERROR:File not found.";
			exit;
		}
		
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control:private",false);
		header("Content-Type:application/zip");
		header("Content-Disposition: attachment;filename=".basename($archiveName).";");
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".filesize($archiveName));
		readfile("$archiveName");
	}

}
?>