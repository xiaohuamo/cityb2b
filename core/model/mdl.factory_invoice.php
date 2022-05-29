<?php
// download fpdf class (http://fpdf.org)
require(DOC_DIR."static/fpdf/chinese.php");
//require('chinese.php');
define('eol', PHP_EOL);

class mdl_factory_invoice
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

        $this->businessname='';
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

    function setFontSize($value = 9)
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


    public function setBusinessName($businessname)
    {
        $this->businessname = $businessname;

    }

    public function setABN($abn)
    {
        $this->abn = $abn;

    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

    }

    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;

    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

    }

    public function setDate($date1)
    {
        $this->date1 = $date1;

    }

    public function setUserName($userName)
    {
        $this->userName = $userName;

    }
    public function setFactoryAccount($factoryAccount) {

        $this->factoryAccount = $factoryAccount;
    }

    public function setUser_Code($user_code) {

        $this->user_code = $user_code;
    }


    public  function setUser($user, $userABN)
    {
        $this->user = $user;
        $this->userABN = $userABN;
    }

    public function setLogo($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    function Header()
    {
        // Logo
    //    var_dump($this->logoPath);exit;
        if ($this->logoPath) {
            $this->Image($this->logoPath, 160, 6, 30);
        }
        //$this->SetFont('','U',22);
        $this->SetFont('Arial','B',20);
        $this->Cell(50,6, $this->businessname);
        $this->Ln();
        $this->SetFont('Arial','B',10);
        $this->Cell(60,7,'A.B.N '.$this->abn.'  ' .'Tel: '.$this->phone.'  Fax:',0,1,'L');

        $this->SetFont('Arial','',10);
        if($this->businessId ==319188) {
            $this->Cell(60,5,'LINCENSE NO: P00950',0,1,'L');
        }else{
            $this->Cell(60,5,'',0,1,'L');
        }
        $this->Cell(60,5,$this->factory['address'],0,1,'L');
         $this->Cell(60,5,'',0,1,'L');




        $this->SetFont('Arial','B',20);
        $this->Cell(190,8,'TAX INVOICE',0,1,'R');
        $this->SetFont('Arial','B',12);

        $this->Cell(1);
        $this->Cell(80,10,'INVOICE TO ',1,0,'C');
        $this->Cell(95);
        $this->SetFont('Arial','',10);
        $this->Cell(15,5,'Invoic Number:  '.$this->orderId,0,1,'R');


        $this->SetFont('Arial','B',10);
        $this->Cell(190,5,'Date: '. $this->date1,0,1,'R');
        $this->ln(2);


        $this->row("CODE: ".$this->user_code['nickname'], 0.7, 0, "L", 6);
        $this->SetFont('Arial','B',10);
        $this->row("Ship TO : ".$this->factoryABN['untity_name'], 0.3, 0, "L", 6);
        $this->SetFont('Arial','','');

        $this->ln();
        $this->row($this->userName, 0.7, 0, "L", 6);
        $this->row("Business Name: ".$this->factoryABN['untity_name'], 0.3, 0, "L", 6);
        $this->ln();
        if($this->userABN['ABNorACN']!='00000000000'){
            $abnn=$this->userABN['ABNorACN'];
        }else{
            $abnn='';
        }
        $this->row("ABN/ACN: ".$abnn, 0.7, 0, "L", 6);
        $this->row("ABN/ACN: ".$this->factoryABN['ABNorACN'], 0.3, 0, "L", 6);
        $this->ln();


        $this->row("Address: ".$this->user['address'], 0.7, 0, "L", 6);
        $this->ln();
        /*
        $this->row('', 0.7, 0, "L", 6);
        $this->row("Phone: ".$this->factory['phone'], 0.3, 0, "L", 6);
         $this->ln(10);
        $this->row('Ship To', 0.7, 0, "L", 6);
         $this->ln();
        $this->row("First name: ".$this->order['first_name'], 0.3, 0, "L", 6);
        $this->row("Last name: ".$this->order['last_name'], 0.4, 0, "L", 6);
        $this->row("Mobile: ".$this->order['phone'], 0.3, 0, "L", 6);

        $this->row("Post Code: ".$this->order['postalcode'], 0.3, 0, "L", 6);
        */



    }

    function Footer()
    {
        $this->SetY(-40);
        $this->row("", 1, 1, 'C', 0.1);

        $this->row("MTV NO :0100/1404/3370/3371", 0.7, 0, "L", 6);

        $this->row("Temperature: 1.9", 0.3, 0, "L", 6);

        $this->ln();
        $this->row("No Claim will be recognised unless received with 24 hours date of delivery.", 0.7, 0, "L", 6);
        $this->ln(5);

        $this->row("Acc Name: " . $this->factoryAccount['account_name'], 0.7, 0, "L", 6);
        $this->ln();
        $this->row("BSB: " . $this->factoryAccount['bsb_number'], 0.7, 0, "L", 6);
        $this->ln();
        $this->row("ACC: " . $this->factoryAccount['account_number'], 0.7, 0, "L", 6);
        $this->ln();

        // Position at 1.5 cm from bottom

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

class OrderInvoice
{
    public $user;

    public $userABN;

    public $factory;

    public $factoryAccount;

    public $factoryABN;

    public $order;

    public $items;

    public $title;//标题

    function __construct($order, $items)
    {
        $this->pdf = new pdfGenerator();
        $this->order = $order;
        $this->items = $items;
    }

    function title($value = '', $headerTag = '')
    {

        if (!$headerTag) {
            $headerTag = '2020-12-11';
        }else{
            $headerTag = '2020-12-11';
        }

        $this->pdf->setTitle($value, $headerTag);

        return $this;
    }



    function logoPath($path)
    {
        $this->logoPath = $path;
        return $this;
    }
    function setUser($user, $userABN)
    {
        $this->user = $user;
        $this->userABN = $userABN;
    }
    function setUser_Code($user_Code)
    {
        $this->user_Code = $user_Code;

    }


    function setFactory($factory, $factoryABN, $factoryAccount)
    {
        $this->factory = $factory;
        $this->factoryAccount = $factoryAccount;
        $this->factoryABN = $factoryABN;
    }

    function orderTitle()
    {
        // return 'Invoice-'.$this->order['orderId'];
        return 'DNL Trading PTY LTD';
    }

    function generatePDF()
    {
        $this->pdf->setTitle($this->orderTitle(),'2020-12-11');
        $this->pdf->setLogo(DOC_DIR.$this->logoPath);
        $this->pdf->setBusinessName($this->factoryABN['untity_name']);
        $this->pdf->setABN($this->factoryABN['ABNorACN']);
        $this->pdf->setPhone($this->factory['phone']);
        $this->pdf->setBusinessId($this->factory['id']);
        $this->pdf->setOrderId($this->order['orderId']);
        $this->pdf->setDate(date('Y-m-d ',$this->order['logistic_delivery_date']));
        $this->pdf->setUserName($this->userABN['business_name']);
        $this->pdf->setFactoryAccount($this->factoryAccount);

        $this->pdf->setUser_Code($this->user_Code);
        $this->pdf->setUser($this->user, $this->userABN);

        //var_dump($this->userABN);exit;

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();
        $this->pdf->ln(5);
        //$this->pdf->row("", 1, 1, 'C', 0.1);

        /*
        $this->pdf->row('Invoice To', 0.7, 0, "L", 6);
        $this->pdf->row('Invoice From', 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Business Name: ".$this->userABN['untity_name'], 0.7, 0, "L", 6);
        $this->pdf->row("Business Name: ".$this->factoryABN['untity_name'], 0.3, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("ABN/ACN: ".$this->userABN['ABNorACN'], 0.7, 0, "L", 6);
        $this->pdf->row("ABN/ACN: ".$this->factoryABN['ABNorACN'], 0.3, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row('', 0.7, 0, "L", 6);
        $this->pdf->row("Phone: ".$this->factory['phone'], 0.3, 0, "L", 6);
        $this->pdf->ln(10);
        $this->pdf->row('Ship To', 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("First name: ".$this->order['first_name'], 0.3, 0, "L", 6);
        $this->pdf->row("Last name: ".$this->order['last_name'], 0.4, 0, "L", 6);
        $this->pdf->row("Mobile: ".$this->order['phone'], 0.3, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Address: ".$this->order['address'], 0.7, 0, "L", 6);
        $this->pdf->row("Post Code: ".$this->order['postalcode'], 0.3, 0, "L", 6);
        $this->pdf->ln(10);
  */


        $this->pdf->ln(5);
        $this->pdf->row("Code ", 0.10, 0, "L", 6);
        $this->pdf->row('Description', 0.40, 0, "L", 6);
        $this->pdf->row('Qty', 0.05, 0, "L", 6);
        $this->pdf->row('Price', 0.10, 0, "L", 6);
        $this->pdf->row('Disc.', 0.10, 0, "L", 6);
        $this->pdf->row('Newprice', 0.10, 0, "L", 6);
        $this->pdf->row('Amount', 0.10, 0, "L", 6);
        $this->pdf->row('GST%', 0.05, 0, "L", 6);
        $this->pdf->ln(6);
        $this->pdf->row("", 1, 1, 'C', 0.1);

        $totalAmount = [
            'total_with_gst' => 0,
            'total_no_gst' => 0,
            'total_gst' => 0,
            'quantity' => 0
        ];
        //var_dump($this->item);exit;
        foreach ($this->items as $item) {
            $itemAmount = $this->calculateAmountWithGst($item['voucher_deal_amount'], $item['customer_buying_quantity'], $item['include_gst']);
            $this->pdf->ln();


            if ($item['menu_en_name']){
                $item_name =$item['menu_en_name'];
            } else{
                $item_name =$item['bonus_title'];
            }
            if($item['guige_des']) {
                $item_name .=' Spec:'.$item['guige1_id'].' '.$item['guige_des'];

            }

            $discount =number_format($item['voucher_original_amount']-$item['voucher_deal_amount'],2);


            //   $this->pdf->row1($item_name, 0.40, 0, "L", 6);


            $title1 =$item_name;
            $title_length=strlen($title1);
            $count =ceil($title_length/45);

            if($count ==1 ){
                $this->pdf->row($item['menu_id'], 0.10, 0, "L", 6);
                $title = mb_substr($title1, 0, 45);
                $this->pdf->row($title, 0.40, 0, "L", 6);
                $this->pdf->row($item['customer_buying_quantity'], 0.05, 0, "L", 6);
                $this->pdf->row($item['voucher_original_amount'], 0.10, 0, "L", 6);
                $this->pdf->row($discount, 0.10, 0, "L", 6);
                $this->pdf->row($item['voucher_deal_amount'], 0.10, 0, "L", 6);
                $this->pdf->row(sprintf("%1\$.2f", $itemAmount['total_with_gst']), 0.10, 0, "L", 6);
                $this->pdf->row($itemAmount['gst%'], 0.05, 0, "L", 6);


            }else{
                for($i=0;$i<$count;$i++) {
                    if ($count==(1+$i)) {
                        $title = substr($title1, $i*45, 45);
                        $this->pdf->row('', 0.1, 0, "L", 6);
                        $this->pdf->row($title, 0.40, 0, 'L', 6);

                    }else{
                        if($i==0) {
                            $title = substr($title1, $i*45, 45);
                            $this->pdf->row($item['menu_id'], 0.10, 0, "L", 6);
                            $this->pdf->row($title, 0.4, 0, 'L', 6);
                            $this->pdf->row($item['customer_buying_quantity'], 0.05, 0, "L", 6);
                            $this->pdf->row($item['voucher_original_amount'], 0.10, 0, "L", 6);
                            $this->pdf->row($discount, 0.10, 0, "L", 6);
                            $this->pdf->row($item['voucher_deal_amount'], 0.10, 0, "L", 6);
                            $this->pdf->row(sprintf("%1\$.2f", $itemAmount['total_with_gst']), 0.10, 0, "L", 6);
                            $this->pdf->row($itemAmount['gst%'], 0.05, 0, "L", 6);
                            $this->pdf->ln(5);

                        }else{
                            $title = substr($title1, $i*45, 45);
                            $this->pdf->row('', 0.10, 0, "L", 6);
                            $this->pdf->row($title, 0.4, 0, 'L', 6);
                            $this->pdf->row('', 0.50, 0, "L", 6);

                            $this->pdf->ln(5);

                        }


                    }


                }
            }




            $totalAmount['total_with_gst'] += $itemAmount['total_with_gst'];
            $totalAmount['total_no_gst'] += $itemAmount['total_no_gst'];
            $totalAmount['total_gst'] += $itemAmount['total_gst'];
            $totalAmount['quantity'] += $itemAmount['quantity'];
        }
        $totalCountofItemPrintPerPage = 10;
        if(count($this->items)<=$totalCountofItemPrintPerPage) {
            $loop1= $totalCountofItemPrintPerPage-count($this->items);
            for($i=0;$i<$loop1;$i++) {
                $this->pdf->ln();
                $this->pdf->row('', 1, 0, "L", 6);

            }

            //var_dump(count($this->items));exit;

        }

        $this->pdf->ln(10);
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->ln(5);

        $this->pdf->Cell(1);
        $this->pdf->Cell(80,16,'Signature Here: ',1,0,'L');
        $this->pdf->Cell(50);


        $this->pdf->Cell(30,5,'Sub Total: ',0,0,'L');
        $this->pdf->Cell(20,5,$this->displayAmount( $totalAmount['total_no_gst']),0,0,'R');


        $this->pdf->ln();
        $this->pdf->Cell(131);
        $this->pdf->Cell(30,5,'GST Include In Total: ',0,0,'L');
        $this->pdf->Cell(20,5,$this->displayAmount( $totalAmount['total_gst']),0,0,'R');
        $this->pdf->ln();

        $this->pdf->ln();
        $this->pdf->Cell(91);
        $this->pdf->Cell(20,5,'Total Item(s):  ',0,0,'L');
        $this->pdf->Cell(20,5,$totalAmount['quantity'],0,0,'L');

        $this->pdf->Cell(30,5,'Total Invoice:  ',0,0,'L');
        $this->pdf->Cell(20,5,$this->displayAmount( $totalAmount['total_with_gst']),0,0,'R');
        $this->pdf->ln();





    }


    function outPutToFile($savePdfName)
    {
        $this->pdf->Output($savePdfName, 'F');
    }

    function outPutToBrowser($savePdfName)
    {
        $this->pdf->Output($savePdfName, "I");
    }

    function calculateAmountWithGst($unitPrice, $quantity, $includeGst) {
        $result = [
            'single_amount' => 0,
            'gst%' => 0,
            'single_gst' => 0,
            'quantity' => $quantity,
            'total_with_gst' => 0,
            'total_no_gst' => 0,
            'total_gst' => 0
        ];
        if($includeGst) {
            $result['single_amount'] = $unitPrice / 11 * 10;
            $result['single_gst'] = $unitPrice / 11;
            $result['total_with_gst'] = $unitPrice * $quantity;
            $result['total_no_gst'] = $result['single_amount'] * $quantity;
            $result['total_gst'] =  $result['single_gst'] * $quantity;
            $result['gst%'] = '10%';
        } else {
            $result['single_amount'] = $unitPrice;
            $result['single_gst'] = 0;
            $result['total_with_gst'] = $unitPrice * $quantity;
            $result['total_no_gst'] = $unitPrice * $quantity;
            $result['total_gst'] =  0;
            $result['gst%'] = '';
        }

        return $result;
    }

    function displayAmount($amount)
    {
        return '$' . sprintf("%1\$.2f", $amount);
    }
}

?>

