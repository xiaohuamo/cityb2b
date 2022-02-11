<?php
// download fpdf class (http://fpdf.org)
require(DOC_DIR."static/fpdf/chinese.php");
//require('chinese.php');
define('eol', PHP_EOL);

class  mdl_factoryReport
{
}

/**
 * Base PDF Class that support Chinese
 */
class pdfGenerator extends PDF_Chinese
{
    private $lineWidthDefault;

    private $lineHeightDefault;

    private $logoPath;

    private $fileTitle;

    private $fontFamilyDefault;

    private $fontSizeDefault;

    private $fontStyleDefault;

    private $chFontFamilyDefault;

    private $chFontSizeDefault;

    private $chFontStyleDefault;

    function pdfGenerator()
    {
        parent::__construct();

        $this->AddGBFont(); // add font to support chinese font

        $this->lineWidthDefault = 190;
        $this->lineHeightDefault = 10;

        $this->fileTitle = '';
        $this->headerTag = '';

        $this->logoPath = DOC_DIR.'pic/logo.jpg';

        $this->fontFamilyDefault = 'Arial';
        $this->fontSizeDefault = 9;
        $this->fontStyleDefault = '';

        $this->chFontFamilyDefault = 'GB';
        $this->chFontSizeDefault = 9;
        $this->chFontStyleDefault = '';
    }

    function isEnglish($string)
    {
        if (preg_match("/\p{Han}+/u", $string)) {
            $this->SetFont($this->chFontFamilyDefault, $this->chFontStyleDefault, $this->chFontSizeDefault);

            return false;
        } else {
            $this->SetFont($this->fontFamilyDefault, $this->fontStyleDefault, $this->fontSizeDefault);

            return true;
        }
    }

    function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    function setFontSize($value = 10)
    {
        $this->fontSizeDefault = $value;
        $this->chFontSizeDefault = $value;
    }

    function myText($x, $y, $o)
    {
        if ($this->isEnglish($o)) {
            $this->Text($x, $y, $o);
        } else {
            $this->Text($x, $y, iconv("UTF-8", "gbk//IGNORE", $o));
        }
    }

    public function setTitle($title, $headerTag = '')
    {
        $this->fileTitle = $title;
        $this->headerTag = $headerTag;
    }

    public function setLogo($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    function Header()
    {
        // Logo
        //var_dump($this->logoPath);exit;
        if ($this->logoPath) {
            $this->Image($this->logoPath, 10, 6, 20);
        }
        $this->setFontSize(18);
        // Title
        $this->row('', 0.1, 0);
        $this->row($this->fileTitle, 0.6, 0, 'C');
        $this->setFontSize(12);
        $this->row($this->headerTag, 0.4, 0, 'C');

        // Line break
        $this->Ln(20);
    }

    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->PageNo(), 0, 0, 'C');
    }

    function BasicTable($header, $data, $cellWidth = null)
    {
        $columnCount = sizeof($header);

        if (! $cellWidth) {
            $cellWidth = array_fill(0, $columnCount, 1 / $columnCount);
        }
        //Header
        for ($i = 0; $i < $columnCount; $i++) {
            $this->row($header[$i], $cellWidth[$i], 1, 'C');
        }
        $this->Ln();

        foreach ($data as $row) {
            for ($n = 0; $n < $columnCount; $n++) {
                $this->row($row[$n], $cellWidth[$n], 1);
            }
            $this->Ln();
        }
    }

    // Page header

    /**
     * @param string
     * @param integer
     * @param integer
     * @param string
     * @return None
     */
    function row($txt, $width = 1, $border = 0, $align = '', $height = 0)
    {
        if ($this->isEnglish($txt)) {
            $this->rowEn($txt, $width, $border, $align, $height);
        } else {
            $this->rowCh($txt, $width, $border, $align, $height);
        }
    }

    // Page footer

    /**
     * @param string   the test
     * @param double   percentage of total width
     * @param int      border enable
     * @param string   text align R L C
     * @return none
     */
    function rowCh($txt, $width = 1, $border = 0, $align = '', $height = 0)
    {
        $ln = ($width == 1) ? 1 : 0;

        $height = ($height > 0) ? $height : $this->lineHeightDefault;

        $this->Cell($this->lineWidthDefault * $width, $height, iconv("UTF-8", "gbk//IGNORE", $txt), $border, $ln, $align);
    }

    // Simple table

    /**
     * @param string   the test
     * @param double   percentage of total width
     * @param int      border enable
     * @param string   text align R L C
     * @return none
     */
    function rowEn($txt, $width = 1, $border = 0, $align = '', $height = 0)
    {
        $ln = ($width == 1) ? 1 : 0;

        $height = ($height > 0) ? $height : $this->lineHeightDefault;

        $this->Cell($this->lineWidthDefault * $width, $height, $txt, $border, $ln, $align);
    }

    // Better table

    function ImprovedTable($header, $data, $cellWidth = null)
    {
        $columnCount = sizeof($header);

        if (! $cellWidth) {
            $cellWidth = array_fill(0, $columnCount, 1 / $columnCount);
        }

        //Header
        for ($i = 0; $i < $columnCount; $i++) {
            $this->row($header[$i], $cellWidth[$i], 1, 'C');
        }
        $this->Ln();
        foreach ($data as $row) {
            for ($n = 0; $n < $columnCount; $n++) {
                $this->row($row[$n], $cellWidth[$n], 'LR');
            }
            $this->Ln();
        }
        // Closing line
        $this->Cell(array_sum($row), 0, '', 'T');
        $this->Ln();
    }

    //HTML parser
    function WriteHTML($html)
    {
        // HTML parser
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e);
                } else {
                    $this->Write(5, $e);
                }
            } else {
                // Tag
                if ($e[0] == '/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                } else {
                    // Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = [];
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function PutLink($URL, $txt)
    {
        // Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    function SetStyle($tag, $enable)
    {
        // Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (['B', 'I', 'U'] as $s) {
            if ($this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }

    function CloseTag($tag)
    {
        // Closing tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, false);
        }
        if ($tag == 'A') {
            $this->HREF = '';
        }
    }

    function OpenTag($tag, $attr)
    {
        // Opening tag
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, true);
        }
        if ($tag == 'A') {
            $this->HREF = $attr['HREF'];
        }
        if ($tag == 'BR') {
            $this->Ln(5);
        }
    }
}
/*
 * Class 订单流水PDF
 */

class OrderInfoReport
{
    public $title;//标题

    public $OrderData;

    public $startTime;

    public $endTime;

    public $customer_delivery_date;

    public $tradingName;

    function __construct()
    {
        $this->pdf = new pdfGenerator();
    }

    function setStarttime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    function setEndtime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    function logoPath($path)
    {
        $this->logoPath = $path;
        return $this;
    }
    function setCustomer_delivery_date($customer_delivery_date)
    {
        $this->customer_delivery_date = $customer_delivery_date;

        return $this;
    }
    function setSepratePage($sepratePage)
    {
        $this->sepratePage = $sepratePage;

        return $this;
    }


    function title($value = '', $headerTag = '')
    {
        if (! $headerTag) {
            $headerTag =  'Delivery Date '.$this->customer_delivery_date;
        }

        $this->pdf->setTitle($value, $headerTag);

        return $this;
    }



    function OrderData($value = '')
    {
        $this->OrderData = $value;

        return $this;
    }

    function OrderDataEveryChannel($value = '')
    {
        $this->OrderDataEveryChannel = $value;

        return $this;
    }

    function generatePDFSingleItemBuyingList()
    {

        $this->pdf->setLogo(DOC_DIR.$this->logoPath);

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();
   
        $first_rec =1;
		$limitNameLength=60;
		 
        foreach ($this->OrderData as $key => $order) {


       if($first_rec ) {
		   $this->pdf->ln(20);
            //orderId//first_name+last_name//phone//status//payment//customer_delivery_option
            $this->pdf->setFontSize(12);
            $this->pdf->row('Item Code: '.$order['menu_id'], 0.2, 0, 'C', 12);
            $this->pdf->row(' Name: '.$order['menu_en_name'].' '.$order['guige_des'], 0.7, 0, 'C', 12);
               $this->pdf->setFontSize(14);
            $this->pdf->row(' Unit:'.$order['unit'], 0.1, 0, 'C', 12);

            $this->pdf->ln();

		   
        //orderId//first_name+last_name//phone//status//payment//customer_delivery_option

        $this->pdf->row('Tick', 0.05, 0, 'C', 12);
        $this->pdf->row('Id', 0.1, 0, 'C', 12);
        $this->pdf->row('SEQ_No', 0.05, 0, 'C', 12);

        $this->pdf->row('Suburb', 0.10, 0, 'C', 12);
        $this->pdf->row('Truck', 0.05, 0, 'C', 12);
        $this->pdf->row('Name', 0.55, 0, 'C', 12);
        $this->pdf->row('Qty', 0.05, 0, 'C', 12);
        $this->pdf->row('Unit', 0.05, 0, 'C', 12);

        $this->pdf->ln();
        $this->pdf->row("", 1, 1, 'C', 0.1);
		
		$first_rec =0;
	   }
		

      
            $nameLength =strlen($order['Address']);


            if($nameLength>$limitNameLength  ){
                $this->pdf->row("", 1, 1, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');
				   $this->pdf->setFontSize();
				 $this->pdf->setFontSize(8);
                $length =strlen($order['order_id']);
                $this->pdf->row(substr($order['order_id'],$length-6,6), 0.10, 0, 'C');
				  $this->pdf->setFontSize();
                $this->pdf->setFontSize(16);
                $this->pdf->row($order['logistic_sequence_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize();


                $this->pdf->row('', 0.10, 0, 'C');
                $this->pdf->setFontSize(14);
                $this->pdf->row($order['customerName'], 0.55, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row($order['customer_buying_quantity'], 0.05, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['unit'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();
                $this->pdf->setFontSize(8);
                $this->pdf->row($order['address'], 0.6, 0, 'C');
                $this->pdf->row($order['logistic_truck_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row("", 1, 1, 'C', 0.1);


                $this->pdf->ln();



            }else{
                $this->pdf->row("", 1, 1, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');
				 $this->pdf->setFontSize(8);
                $length =strlen($order['order_id']);
                $this->pdf->row(substr($order['order_id'],$length-6,6), 0.10, 0, 'C');
				
                $this->pdf->setFontSize(16);
                $this->pdf->row($order['logistic_sequence_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize();

                $this->pdf->setFontSize(8);
                $this->pdf->row($order['address'], 0.10, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row($order['logistic_truck_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize(14);
                $this->pdf->row($order['customerName'], 0.55, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row($order['customer_buying_quantity'], 0.05, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['unit'], 0.05, 0, 'C');
                $this->pdf->setFontSize();


                $this->pdf->ln();
            }

        }


    }


    function generatePDFSelectedItemsBuyingList()
    {


        $old_menu_id =0;
        $old_guige_id =0;
        $limitNameLength=60;
        //var_dump($this->sepratePage);exit;
        if(!$this->sepratePage) {
            $this->pdf->setLogo(DOC_DIR.$this->logoPath);
            $this->pdf->SetLeftMargin(10);
            $this->pdf->AddPage();

        }
	//	var_dump($this->OrderData );exit;
        foreach ($this->OrderData as $key => $order) {




            if($old_menu_id ==0 || ($old_menu_id != $order['source_menu_id'] ) ||   ($old_menu_id == $order['source_menu_id']  && $old_guige_id != $order['guige1_id'] )) {



                if($this->sepratePage) {

                    $this->pdf->setLogo(DOC_DIR.$this->logoPath);
                    $this->pdf->SetLeftMargin(10);
                    $this->pdf->AddPage();

                }
                if(!$order['menu_en_name']) {
                    $order['menu_en_name'] =$order['menu_cn_name'] ;
                }
                if($old_menu_id !=0)   $this->pdf->ln(15);
                //orderId//first_name+last_name//phone//status//payment//customer_delivery_option
                $this->pdf->setFontSize(12);

                $this->pdf->row('   Item Code:'.$order['menu_id'], 0.2, 0, 'C', 12);
                $this->pdf->row('Name:'.$order['menu_en_name'], 0.7, 0, 'C', 12);
                $this->pdf->setFontSize(14);
                $this->pdf->row(' Unit:'.$order['unit'], 0.1, 0, 'C', 12);
                $this->pdf->setFontSize(12);
                $this->pdf->ln(7);

                //如果含有规格
                if($order['guige1_id']) {
                    $this->pdf->row('Spec Code:'.$order['guige1_id'], 0.2, 0, 'C', 12);
                    $this->pdf->row('Name:'.$order['guige_des'], 0.7, 0, 'C', 12);
                    $this->pdf->row('', 0.1, 0, 'C', 12);
                    $this->pdf->ln();
                }

                $this->pdf->setFontSize(10);
                $this->pdf->row('Tick', 0.05, 0, 'C', 12);
                $this->pdf->row('Id', 0.10, 0, 'C', 12);
                $this->pdf->row('SEQ_No', 0.10, 0, 'C', 12);

                $this->pdf->row('Suburb', 0.15, 0, 'C', 12);
                $this->pdf->row('Truck', 0.05, 0, 'C', 12);
                $this->pdf->row('Customer Name', 0.40, 0, 'C', 12);
                $this->pdf->row('Qty', 0.10, 0, 'C', 12);
                $this->pdf->row('Unit', 0.05, 0, 'C', 12);
                $this->pdf->setFontSize();
                $this->pdf->ln();
                $this->pdf->row("", 1, 1, 'C', 0.1);
            }


            $nameLength =strlen($order['Address']);


            if($nameLength>$limitNameLength  ){
                $this->pdf->setFontSize(10);
                $this->pdf->row("", 1, 1, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');
                $length =strlen($order['order_id']);
                $this->pdf->row(substr($order['order_id'],$length-6,6), 0.10, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['logistic_sequence_No'], 0.10, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row('', 0.15, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['customerName'], 0.40, 0, 'C');
                $this->pdf->row($order['customer_buying_quantity'], 0.10, 0, 'C');
                $this->pdf->row($order['unit'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();
                $this->pdf->row($order['address'], 0.6, 0, 'C');
                $this->pdf->row($order['logistic_truck_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row("", 1, 1, 'C', 0.1);


                $this->pdf->ln();



            }else{
                $this->pdf->row("", 1, 0, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');
                $length =strlen($order['order_id']);
                $this->pdf->row(substr($order['order_id'],$length-6,6), 0.10, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['logistic_sequence_No'], 0.10, 0, 'C');
                $this->pdf->setFontSize();


                $this->pdf->row($order['address'], 0.15, 0, 'C');
                $this->pdf->row($order['logistic_truck_No'], 0.05, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['customerName'], 0.4, 0, 'C');
                $this->pdf->row($order['customer_buying_quantity'], 0.10, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->row($order['unit'], 0.05, 0, 'C');



                $this->pdf->ln();
            }

            $old_menu_id = $order['source_menu_id'];
            $old_guige_id = $order['guige1_id'];


        }






    }


    function generatePDF_totalOrderSummeryForDeliveryDate()
    {

        $this->pdf->setLogo(DOC_DIR.$this->logoPath);

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        $total = 0;


        //orderId//first_name+last_name//phone//status//payment//customer_delivery_option
        $this->pdf->setFontSize(12);
        $this->pdf->row('Tick', 0.05, 0, 'C', 12);
        $this->pdf->row('Type', 0.15, 0, 'C', 12);
        $this->pdf->row('Item ID', 0.10, 0, 'C', 12);
        $this->pdf->row('Spec.Id', 0.10, 0, 'C', 12);
        $this->pdf->row('Item Name', 0.4, 0, 'C', 12);
       

        $this->pdf->row('Quantity', 0.1, 0, 'C', 12);
        $this->pdf->row('Unit', 0.1, 0, 'C', 12);
        $this->pdf->setFontSize();
        $this->pdf->ln();
        $this->pdf->row("", 1, 1, 'C', 0.1);

        $limitCateLength=30;
        $limitNameLength=50;
        foreach ($this->OrderData as $key => $order) {
            $nameLength =strlen($order['bonus_title'].' '.$order['guige_des']);
            $cateLength =strlen($order['category_en_name']);

            if($nameLength>$limitNameLength || $cateLength>$limitCateLength ){
                $this->pdf->row("", 1, 1, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');

                if($cateLength>$limitCateLength  ) {
                    $this->pdf->row('', 0.15, 0, 'C');
                }else{
                  //  $this->pdf->row($order['category_en_name'].'/'.$order['category_cn_name'], 0.20, 0, 'C');
				$this->pdf->row($order['category_en_name'], 0.15, 0, 'C');

                }
                $this->pdf->row($order['menu_id'], 0.10, 0, 'C');
                $this->pdf->row($order['guige1_id'], 0.10, 0, 'C');

                if($nameLength>$limitNameLength  ) {
                    // $this->pdf->row($order['bonus_title'], 0.4, 0, 'C');
                    $this->pdf->row(' ', 0.4, 0, 'C');
                }else{
                    $this->pdf->row($order['menu_en_name'].' '.$order['guige_des'], 0.4, 0, 'C');
                   // $this->pdf->row($order['menu_en_name'], 0.2, 0, 'C');
                }




                $this->pdf->setFontSize(12);
                $this->pdf->row($order['total_quantity'], 0.1, 0, 'C');

                $this->pdf->row($order['unit'], 0.1, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();

                if($nameLength>$limitNameLength && $cateLength>$limitCateLength  ) {
                    $this->pdf->row("category: ".$order['category_en_name'], 0.30, 0, 'C');
                    $this->pdf->row("item Name: ".$order['menu_en_name'].' '.$order['guige_des'], 0.7, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);

                }elseif($nameLength<=$limitNameLength && $cateLength>$limitCateLength) {
                    $this->pdf->row("category: ".$order['category_en_name'], 0.50, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);

                }elseif($nameLength>$limitNameLength && $cateLength<=$limitCateLength) {
                    $this->pdf->row("item Name: ".$order['menu_en_name'].' '.$order['guige_des'], 0.7, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);
                }else{


                }

            }else{
                $this->pdf->row("[  ]", 0.05, 0, 'C');
                $this->pdf->row($order['category_en_name'], 0.15, 0, 'C');

                $this->pdf->row($order['menu_id'], 0.10, 0, 'C');
                $this->pdf->row($order['guige1_id'], 0.10, 0, 'C');

                $this->pdf->row($order['menu_en_name'].' '.$order['guige_des'], 0.4, 0, 'C');
               // $this->pdf->row($order['menu_en_name'], 0.2, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['total_quantity'], 0.1, 0, 'C');

                $this->pdf->row($order['unit'], 0.1, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();
            }

        }


    }


    function generatePDF_totalOrderSummeryAndEachChannelForDeliveryDate()
    {

        $this->generatePDF_totalOrderSummeryForDeliveryDate();

        $old_business_id =0;

        foreach ($this->OrderDataEveryChannel as $key => $order) {

            $new_business_id =$order['business_id'];

            if($old_business_id==0 || $new_business_id != $old_business_id) {//表示新的商家

                $this->pdf->setLogo(DOC_DIR.$this->logoPath);
                $this->pdf->setTitle($order['displayName'],'Delivery Date:'.$this->customer_delivery_date);
                $this->pdf->SetLeftMargin(10);
                $this->pdf->AddPage();
                $total = 0;
                $this->pdf->setFontSize(12);
                $this->pdf->row('Tick', 0.05, 0, 'C', 12);
                $this->pdf->row('Type', 0.20, 0, 'C', 12);
                $this->pdf->row('Code', 0.05, 0, 'C', 12);
                $this->pdf->row('Name', 0.4, 0, 'C', 12);
                $this->pdf->row('name(en)', 0.2, 0, 'C', 12);

                $this->pdf->row('Qty', 0.05, 0, 'C', 12);
                $this->pdf->row('Unit', 0.05, 0, 'C', 12);
                $this->pdf->setFontSize();
                $this->pdf->ln();
                $this->pdf->row("", 1, 1, 'C', 0.1);

                $limitCateLength=30;
                $limitNameLength=60;

            }

            $nameLength =strlen($order['menu_en_name'].' '.$order['guige_des']);
            $cateLength =strlen($order['category_en_name'].'/'.$order['category_cn_name']);

            if($nameLength>$limitNameLength || $cateLength>$limitCateLength ){
                 $this->pdf->setFontSize(9);
				$this->pdf->row("", 1, 1, 'C', 0.1);
                $this->pdf->row("[  ]", 0.05, 0, 'C');

                if($cateLength>$limitCateLength  ) {
                    $this->pdf->row('', 0.20, 0, 'C');
                }else{
                    $this->pdf->row($order['category_en_name'].'/'.$order['category_cn_name'], 0.20, 0, 'C');


                }
                $this->pdf->row($order['menu_id'], 0.05, 0, 'C');

                if($nameLength>$limitNameLength  ) {
                    // $this->pdf->row($order['bonus_title'], 0.4, 0, 'C');
                    $this->pdf->row($order['menu_en_name'].' '.$order['guige_des'], 0.6, 0, 'C');
                }else{

                    $this->pdf->row($order['menu_en_name'].' '.$order['guige_des'], 0.6, 0, 'C');
                }




                $this->pdf->setFontSize(12);
                $this->pdf->row($order['total_quantity'], 0.05, 0, 'C');
                $this->pdf->setFontSize(14);
                $this->pdf->row($order['unit'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();

                if($nameLength>$limitNameLength && $cateLength>$limitCateLength  ) {
                    $this->pdf->row("category: ".$order['category_en_name'].'/'.$order['category_cn_name'], 0.30, 0, 'C');
                    $this->pdf->row("item Name: ".$order['bonus_title'], 0.7, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);

                }elseif($nameLength<=$limitNameLength && $cateLength>$limitCateLength) {
                    $this->pdf->row("category: ".$order['category_en_name'].'/'.$order['category_cn_name'], 0.50, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);

                }elseif($nameLength>$limitNameLength && $cateLength<=$limitCateLength) {
                    $this->pdf->row("item Name: ".$order['bonus_title'], 0.7, 0, 'C');
                    $this->pdf->ln();
                    $this->pdf->row("", 1, 1, 'C', 0.1);
                }else{


                }

            }else{
                $this->pdf->row("[  ]", 0.05, 0, 'C');
                $this->pdf->row($order['category_en_name'].'/'.$order['category_cn_name'], 0.20, 0, 'C');

                $this->pdf->row($order['menu_id'], 0.05, 0, 'C');


                $this->pdf->row($order['menu_en_name'].' '.$order['guige_des'], 0.6, 0, 'C');
                $this->pdf->setFontSize(12);
                $this->pdf->row($order['total_quantity'], 0.05, 0, 'C');
                $this->pdf->setFontSize(14);
                $this->pdf->row($order['unit'], 0.05, 0, 'C');
                $this->pdf->setFontSize();
                $this->pdf->ln();
            }



            $old_business_id =$order['business_id'];
        }







    }


    function outPutToFile($savePdfName)
    {
        $this->pdf->Output($savePdfName, 'F');
    }

    function outPutToBrowser($savePdfName)
    {
        $this->pdf->Output($savePdfName, "I");
    }
}

/*
 * Class 订单流水PDF
 */

class shippingLabel
{
    public $title;//标题

    public $OrderData;

    public $OrderDataEveryChannel;

    public $logoPath;

    public $startTime;

    public $endTime;

    public $returnAddress;

    private $fitInPage;

    function __construct()
    {
        $this->pdf = new pdfGenerator();
        $this->fitInPage = false;
        $this->logoPath = 'themes/zh-cn/images/logo.png';
    }
    public function setLogo($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    function setStarttime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }




    function setEndtime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function fitInPage($value)
    {
        $this->fitInPage = $value;

        return $this;
    }

    function title($value = '', $headerTag = '')
    {

        if (! $headerTag) {
            $headerTag = $this->startTime." To ".$this->endTime;
        }

        $this->pdf->setTitle($value, $headerTag);

        return $this;
    }

    function setReturnAddress($address)
    {
        $this->returnAddress = $address;

        return $this;
    }

    function OrderData($value = '')
    {
        $this->OrderData = $value;

        return $this;
    }

    function OrderDataEveryChannel($value = '')
    {
        $this->OrderDataEveryChannel = $value;

        return $this;
    }



    function logoPath($path)
    {
        $this->logoPath = $path;
        return $this;
    }

    function outPutToFile($savePdfName)
    {
        $this->pdf->Output($savePdfName, 'F');
    }

    function outPutToBrowser($savePdfName)
    {
        $this->pdf->Output($savePdfName, "I");
    }

    function generatePDF()
    {
        $this->pdf->setLogo(DOC_DIR.$this->logoPath);

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        foreach ($this->OrderData as $key => $order) {

            $this->pdf->Image($order['redeemQRCode'], 181, 22, 20);

            $orderID = "ORDER ID: ".$order['orderId'];
            $this->pdf->row($orderID, 0.4, 0, "L", 6);

            $this->pdf->row("NAME: ", 0.1, 0, "R", 6);
            $name = $order['first_name']." ".$order['last_name'];
            $this->pdf->row($name, 0.2, 0, "L", 6);

            $phone = "PHONE: ".$order['phone'];
            $this->pdf->row($phone, 0.3, 0, "L", 6);

            $this->pdf->ln();

            //$this->pdf->row($order['message_to_business'], 0.7, 0, 'L', 6);
            $this->pdf->row('', 0.7, 0, 'L', 6);


            $payment = $order['payment'];

            $status = $order['status'];
            $status = ($status == 0) ? 'Not Paid' : 'Paid';

            $customer_delivery_option = $order['customer_delivery_option'];
            if ($customer_delivery_option == '1') {
                $customer_delivery_option = 'Delivery';
            } elseif ($customer_delivery_option == '2') {
                $customer_delivery_option = 'Pick up';
            } else {
                $customer_delivery_option = 'No need Delivery';
            }

            $this->pdf->row($payment."|".$status."|".$customer_delivery_option, 0.3, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("", 1, 1, 'C', 0.1);
            $this->pdf->ln();



            $this->pdf->setFontSize(12);
            $this->pdf->row('Delivery Date', 0.25, 0, 'C', 12);
            $this->pdf->row('Seq-No', 0.2, 0, 'C', 12);
            $this->pdf->row('truckNo', 0.15, 0, 'C', 12);
            $this->pdf->row('stopNo', 0.15, 0, 'C', 12);
            $this->pdf->row('Suppliers Count', 0.25, 0, 'C', 12);
            $this->pdf->setFontSize();

            $this->pdf->ln(10);

            $logistic_truck_No = $order['logistic_truck_No'];
            $logistic_delivery_date = date('Y-m-d', $order['logistic_delivery_date']);
            $logistic_stop_No = $order['logistic_stop_No'];
            $logistic_sequence_No = $order['logistic_sequence_No'];
            $logistic_suppliers_count = $order['logistic_suppliers_count'];
            $this->pdf->setFontSize(18);
            $this->pdf->row($logistic_delivery_date, 0.25, 1, "C", 15);
            $this->pdf->setFontSize(40);
            $this->pdf->row($logistic_sequence_No, 0.2, 1, "C", 15);
            $this->pdf->setFontSize(18);
            $this->pdf->row('D'.$logistic_truck_No, 0.15, 1, "C", 15);
            $this->pdf->row('S'.$logistic_stop_No, 0.15, 1, "C", 15);
            $this->pdf->row($logistic_suppliers_count, 0.25, 1, 'C', 15);
            $this->pdf->setFontSize();
            $this->pdf->ln();

            $this->pdf->setFontSize(12);
            $this->pdf->ln(5);

            $this->pdf->row("Address To:", 0.2, 0, "C", 12);
            $address = $order['address'];

            $this->pdf->setFontSize(12);
            $this->pdf->row($address, 0.8, 1, "C", 15);
            $this->pdf->setFontSize();

            if($order['message_to_business']) {
                $this->pdf->ln(20);

                $this->pdf->row("Notes:", 0.2, 0, "C", 15);



                $this->pdf->setFontSize(12);
                $this->pdf->row($order['message_to_business'], 0.8, 1, "C", 15);
                $this->pdf->setFontSize();
            }
            $this->pdf->ln(10);

            $this->pdf->ln(10);

            $this->pdf->row("Suppliers info:", 0.2, 0, "C", 12);

            $logistic_suppliers_info = $order['logistic_suppliers_info'];
            $this->pdf->setFontSize(12);
            $this->pdf->row($logistic_suppliers_info, 0.8, 1, "C", 15);
            $this->pdf->setFontSize();











            // $this->pdf->setFontSize(12);
            // $this->pdf->row($logistic_suppliers_info, 1, 1, "C", 15);

            // $this->pdf->setFontSize();
            $this->pdf->ln();

            foreach ($order['items'] as $item) {
                $this->pdf->row("", 0.05);
                $this->pdf->setFontSize(14);
                $title = mb_substr($item['bonus_title'], 0, 60)." ".$item['guige_des'];
                $this->pdf->row('【  】'.$title.'(Qty'.$item['customer_buying_quantity'].')', 0.85, 0, 'L');
                $this->pdf->setFontSize(16);
                $this->pdf->row('x'.$item['customer_buying_quantity'], 0.1, 0, 'C');

                $this->pdf->ln();
                $this->pdf->setFontSize();
            }

            foreach ($order['hasLottery'] as $item) {
                $this->pdf->row("", 0.05);

                $title = "奖品合单：".$item['lottery_sub_name'].",".$item['lottery_sub_details'];
                $this->pdf->row(trim($title), 0.85, 0, 'L');
                $this->pdf->row($item['lid']."-".$item['lcid'], 0.1, 0, 'C');
                $this->pdf->ln();
            }


            if ($this->fitInPage == true) {
                $d = $this->OrderData;
                $nextHeight = 10 * (sizeof($d[$key + 1]['items']) + sizeof($d[$key + 1]['hasLottery']) + 7);
                if ($this->pdf->h - $this->pdf->y < $nextHeight) {
                    $this->pdf->AddPage();
                } else {
                    $this->pdf->ln();
                }
            } else {
                $this->pdf->AddPage();
            }
        }
    }
}



?>

