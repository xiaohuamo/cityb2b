<?php
// download fpdf class (http://fpdf.org)
require(DOC_DIR."static/fpdf/chinese.php");
//require('chinese.php');
define('eol', PHP_EOL);

class mdl_factory_invoice_dnl
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

    public function setTotalAmount($amount)
    {
        $this->totalAmount = $amount;

    }

    public function setaccountPayment_period($type)
    {
        $this->accountPayment_period = $type;

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

    public function setInvoiceId($invoiceId)
    {
        $prestr='';
        if(strlen($invoiceId)<=8){
            for($i=strlen($invoiceId);$i<8;$i++) {
                $prestr .='0';
            }
            $invoiceId =$prestr .$invoiceId;

        }
        $this->invoiceId = $invoiceId;

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

    function SetDash($black=null, $white=null)

    {

        if($black!==null)

            $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);

        else

            $s='[] 0 d';

        $this->_out($s);

    }
	
	public  function setUser($user, $userABN)
    {
        $this->user = $user;
        $this->userABN = $userABN;
    }
    public  function setFinished()
    {
        $this->isFinished=true;

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
            $this->Image($this->logoPath, 4, 0, 50,50,'PNG');
        }
        $this->SetFont('Arial','B',14);
        $this->row("", 0.7, 0, "L", 6);
        $this->row("A.B.N .$this->abn", 0.3, 0, "L", 6);
        $this->ln(6);
        $this->row("", 0.7, 0, "L", 6);
        $this->row("Https://www.dnlgroup.com.au", 0.3, 0, "L", 6);
        $this->ln(6);
        $this->row("", 0.7, 0, "L", 6);
        $this->row("30 Blaxland Ave, Thomastown 3074", 0.3, 0, "L", 6);
        $this->ln(6);
        $this->row("  ", 0.32, 0, "L", 6);
      //  $this->setFontSize(30);
        $this->SetTextColor(255,0,0);
        $this->SetFont('Arial','B',30);
        $this->Cell(100,10,"DNL FOOD", 0, 0, "L");
        $this->setFontSize();
        $this->SetTextColor();
        $this->row("TEL:03 93988222 MP:0450599336", 0.122, 0, "R", 6);

        $this->ln(6);
        $this->row("", 0.7, 0, "L", 6);
        $this->row("Email:info@dnlgroup.com.au", 0.3, 0, "L", 6);

        $this->ln(6);
        $this->row("", 0.7, 0, "L", 6);
        $this->SetFont('Arial','B',10);
        $this->Cell(190,6,"BSB:063-132 ACC:1159 9479", 0, 0, "L");
        //$this->SetFont('','U',22);


        $this->ln(6);

           // Background color
        $this->SetFillColor(31,190,213);
        // Title
        $this->Cell(0,1,"",0,1,'L',true);
        // Line break
        $this->Ln(4);


		
		$this->SetFont('Arial','',20);
		$this->Cell(170,5,'TAX INVOICE',0,1,'R');
        $this->SetFont('Arial','',12);
		

		$this->Cell(0,0,'Invoice To: ',0,0,'L');
		$this->Cell(95);
		$this->SetFont('Arial','',10);
        $this->ln(6);
        if(strlen($this->userABN['business_name'])>30) {
            $this->SetFont('Arial','b',12);
        }else{
            $this->SetFont('Arial','b',14);
        }

       // $this->row($this->user_code['nickname'], 0.5, 0, "L", 6);
        $this->cell(0,0,substr($this->userABN['business_name'],0,30), 0, 0, "L");
        if(strlen($this->userABN['business_name'])>30) {

            $this->ln(6);
            $this->cell(0,0,substr($this->userABN['business_name'],31,35), 0, 0, "L");

        }

        $this->SetFont('Arial','',10);
        $this->ln();
        $this->row("", 0.5, 0, "L", 6);
        $this->row("Amount", 0.17, 0, "L", 6);
        $this->row("Date", 0.17, 0, "L", 6);
        $this->row("Invoice", 0.16, 0, "L", 6);
        $this->ln(5);
        $this->row("", 0.5, 0, "L", 6);
        $this->row( $this->totalAmount, 0.17, 0, "L", 6);
        $this->row($this->date1, 0.17, 0, "L", 6);

        $this->row($this->invoiceId, 0.16, 0, "L", 6);

        $this->ln();
      //  $this->row("ACC NO: ".$this->user_code['nickname'].'  '.$this->user['id'], 0.5, 0, "L", 6);
        $this->row("ACC NO: ".$this->user['id'], 0.5, 0, "L", 6);
        $this->row("Total Outstanding", 0.17, 0, "L", 6);
        $this->row("Payment Term", 0.17, 0, "L", 6);
        $this->row("Reference", 0.16, 0, "L", 6);

        $this->ln();
        $this->row($this->user['address'], 0.5, 0, "L", 6);
        $this->row('-', 0.17, 0, "L", 6);
        $this->row("$this->accountPayment_period", 0.17, 0, "L", 6);
        $this->row("", 0.16, 0, "L", 6);

        $this->ln();
        $this->row("Contact: ". $this->user['person_first_name'].' '.$this->user['person_last_name'].' '.$this->user['phone'], 1, 0, "L", 6);



        // Background color
        $this->SetFillColor(183,222,232);


        $this->ln(3);
        $this->SetFont('Arial','B',12);
        $this->Cell(19,8," Item", 0, 0, 'L',true);
        $this->Cell(95,8,"Description", 0, 0, 'L',true);
        $this->Cell(19,8,"Qty ", 0, 0, 'R',true);
        $this->Cell(19,8,"Unit", 0, 0, 'R',true);
        $this->Cell(19,8,"Price ", 0, 0, 'R',true);
        $this->Cell(19,8,"Total ", 0, 0, 'R',true);





        $this->ln(8);


        // Background color
        $this->SetFillColor(0,0,0);
        // Title
        $this->Cell(0,1,"",0,1,'L',true);
        // Line break
        $this->Ln(1);


        // ACC NO:AWG Total Outstanding Payment Term Reference
      /*
        $this->Cell(20,6,$this->factoryABN['untity_name'],0,0,'L');


        $this->row($this->factoryABN['untity_name'], 0.3, 0, "L", 6);






        $this->ln(10);
        $this->Cell(15,5,'Amount:  '.$this->orderId,0,1,'R');
		
		
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
	 */
     
		
		
    }

    function Footer()
    {
        if($this->isFinished) {
            $this->SetY(-60);
            $this->Cell(115, 6, ' ', 0, 0, 'L');
            $this->Cell(25, 6, 'TIME ', 0, 0, 'L');
            $this->Cell(25, 6, 'MTV ', 0, 0, 'L');
            $this->Cell(25, 6, 'TEMPT ', 0, 0, 'L');
            $this->ln(10 );
            $this->SetLineWidth(0.3);
            $this->SetDrawColor(219, 215, 194);

            $this->Line(135, 242, 145, 242);
            $this->Line(158, 242, 168, 242);
            $this->Line(188, 242, 198, 242);
          //  $this->Line(156, 235, 185, 20);

            $this->SetDrawColor(0, 0, 0);
            //画虚线
            $this->SetLineWidth(0.8);
            $this->SetDash(2, 2); //5mm on, 5mm off
            $this->Line(10, 244, 200, 244);


            //第一行
            $this->row("Invoice&Delivery To:" , 0.4, 0, "L", 6);
            $this->row("Delivered By:", 0.3, 0, "L", 6);
            $this->row("Invoice:". $this->invoiceId, 0.3, 0, "L", 6);
            $this->ln(7);


            //第二行
            $this->row($this->user_code['nickname'], 0.4, 0, "L", 6);
            $this->row("Date:" . $this->date1, 0.3, 0, "L", 6);
            $this->row("TIME", 0.3, 0, "L", 6);
            $this->ln(7);

            //第三行
            $this->row('Signatrue:', 0.4, 0, "L", 6);
            $this->row("CASH", 0.3, 0, "L", 6);

            $this->row("MTV", 0.3, 0, "L", 6);
            $this->ln(7);


            $this->SetDash();
            $this->SetLineWidth(0.3);
            $this->SetDrawColor(219, 215, 194);
            $this->Rect(28, 259, 25, 8);





            //第四行


            $this->row("Amount".'   '. $this->totalAmount, 0.4, 0, "L", 6);

            $this->row("CHQ", 0.3, 0, "L", 6);
            $this->row("TEMPT", 0.3, 0, "L", 6);

            $this->SetLineWidth(0.3);
            $this->SetDrawColor(219, 215, 194);

            $this->Line(98, 266, 127, 266);//CASH LINE
            $this->Line(98, 273, 127, 273); //CHEQUE LINE
            $this->Line(156, 266, 185, 266); // MTV LINE
            $this->Line(156, 260, 185, 260);//TIME LINE
            $this->Line(156, 273, 185, 273); //TEMPT LINE
            //第5行







            $this->ln(7);

        }
        $this->ln(7);
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

    public $notice;

    public $special_info;

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

    function setNotice($notice)
    {
        $this->notice = $notice;

    }

    function setSpecial($special_info)
    {
        $this->special_info = $special_info;

    }
   function setUser_Code($user_Code)
    {
        $this->user_Code = $user_Code;
       
    }
    function setTotalAmount($amount)
    {
        $this->totalAmount = $amount;

    }

    function setFactory($factory, $factoryABN, $factoryAccount)
    {
        $this->factory = $factory;
        $this->factoryAccount = $factoryAccount;
        $this->factoryABN = $factoryABN;
    }

     function setaccountPayment_period($type)
    {
        $this->accountPayment_period = $type;

    }

    function orderTitle()
    {
       // return 'Invoice-'.$this->order['orderId'];
	   return 'DNL Trading PTY LTD';
    }

    function generatePDFDNL()
    {
        $this->pdf->setTitle($this->orderTitle(),'2020-12-11');
        $this->pdf->setLogo(DOC_DIR.$this->logoPath);
		$this->pdf->setBusinessName($this->factoryABN['untity_name']);
		$this->pdf->setABN($this->factoryABN['ABNorACN']);
		$this->pdf->setPhone($this->factory['phone']);
		$this->pdf->setBusinessId($this->factory['id']);
		$this->pdf->setOrderId($this->order['orderId']);
        if($this->order['xero_invoice_id']){
            $this->pdf->setInvoiceId($this->order['xero_invoice_id']);
        }else{
            $this->pdf->setInvoiceId('INV-C'.$this->order['id']);
        }




		$this->pdf->setDate(date('d/m/Y ',$this->order['logistic_delivery_date']));
		$this->pdf->setUserName($this->userABN['business_name']);
		$this->pdf->setFactoryAccount($this->factoryAccount);
        $this->pdf->setTotalAmount($this->totalAmount);
        $this->pdf->setaccountPayment_period($this->accountPayment_period);
    	$this->pdf->setUser_Code($this->user_Code);
		$this->pdf->setUser($this->user, $this->userABN);

		//var_dump($this->userABN);exit;

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();
		 $this->pdf->ln(5);
		//$this->pdf->row("", 1, 1, 'C', 0.1);


        // Arial 12


    foreach ($this->items as $item) {


        $itemAmount = $this->calculateAmountWithGst($item['voucher_deal_amount'], $item['customer_buying_quantity'], $item['include_gst']);



        if ($item['menu_en_name']){
            $item_name =$item['menu_en_name'];
        } else{
            $item_name =$item['bonus_title'];
        }
        if($item['guige_des']) {
            $item_name .=' '.$item['guige_des'];

        }




        $title1 =$item_name;
        $title_length=strlen($title1);
        $count =ceil($title_length/45);

        if($count ==1 ){
            $this->pdf->row($item['menu_id'], 0.10, 0, "L", 6);
            $title = mb_substr($title1, 0, 45);
            $this->pdf->row($title, 0.50, 0, "L", 6);
            $this->pdf->row($item['customer_buying_quantity'].' ', 0.1, 0, "R", 6);
            $this->pdf->row($item['unit'], 0.1, 0, "R", 6);
            $this->pdf->row($item['voucher_deal_amount'].'  ', 0.10, 0, "R", 6);
            $subtotal = sprintf("%1\$.2f", $item['customer_buying_quantity']*$item['voucher_deal_amount']);
            $this->pdf->row($subtotal.'  ', 0.10, 0, "R", 6);

        }else{
            for($i=0;$i<$count;$i++) {
                if ($count==(1+$i)) {
                    $title = substr($title1, $i*45, 45);
                    $this->pdf->row('', 0.1, 0, "L", 6);
                    $this->pdf->row($title, 0.50, 0, 'L', 6);

                }else{
                    if($i==0) {
                        $title = substr($title1, $i*45, 45);
                        $this->pdf->row($item['menu_id'], 0.10, 0, "L", 6);
                        $this->pdf->row($title, 0.50, 0, 'L', 6);
                        $this->pdf->row($item['customer_buying_quantity'].' ', 0.1, 0, "R", 6);
                        $this->pdf->row($item['unit'], 0.1, 0, "R", 6);
                        $this->pdf->row($item['voucher_deal_amount'].'  ', 0.10, 0, "R", 6);
                        $subtotal = sprintf("%1\$.2f", $item['customer_buying_quantity']*$item['voucher_deal_amount']);
                        $this->pdf->row($subtotal.'  ', 0.10, 0, "R", 6);
                        $this->pdf->ln(5);

                    }else{
                        $title = substr($title1, $i*45, 45);
                        $this->pdf->row('', 0.10, 0, "L", 6);
                        $this->pdf->row($title, 0.5, 0, 'L', 6);
                        $this->pdf->row('', 0.40, 0, "L", 6);

                        $this->pdf->ln(5);

                    }


                }


            }
        }

        $this->pdf->ln(5);


        // Background color
        $this->pdf->SetFillColor(219,215,194);

        // Title
        $this->pdf->Cell(0,0.2,"",0,1,'L',true);
        // Line break
        $this->pdf->Ln(1);


        $totalAmount['total_with_gst'] += $itemAmount['total_with_gst'];
        $totalAmount['total_no_gst'] += $itemAmount['total_no_gst'];
        $totalAmount['total_gst'] += $itemAmount['total_gst'];
        $totalAmount['quantity'] += $itemAmount['quantity'];


    }



        $this->pdf->setFinished();
		$totalCountofItemPrintPerPage = 10;
		if(count($this->items)<=$totalCountofItemPrintPerPage) {
			$loop1= $totalCountofItemPrintPerPage-count($this->items);
			for($i=0;$i<$loop1;$i++) {
				 $this->pdf->ln();
       //     $this->pdf->row('', 1, 0, "L", 6);

			}

			//var_dump(count($this->items));exit;

		}

        $this->pdf->ln(4);
        $this->pdf->row("", 0.6, 0, 'L', 0.1);

        $this->pdf->row('',0.4, 1, 'C', 0.1);
        $this->pdf->ln(1);


        if($this->user_Code['discountOfInvoice'] >0) {
            $this->pdf->Cell(115);
            $this->pdf->Cell(50,7,'Discount '.$this->user_Code['discountOfInvoice'].'%',0,0,'L');
            //  $this->pdf->SetFont('Arial','B',12);
            $discount_amount = $totalAmount['total_with_gst']*($this->user_Code['discountOfInvoice']/100);
            $this->pdf->Cell(30,7,$this->displayAmount( $discount_amount).'     ',0,0,'R');

        }else{
            $discount_amount =0;
        }




		 $this->pdf->ln();
         $this->pdf->Cell(115);
    	$this->pdf->Cell(50,7,'Boxes     ',0,0,'L');
        $this->pdf->SetFont('Arial','B',12);
        $this->pdf->Cell(30,7,'Total '.$this->displayAmount( $totalAmount['total_with_gst']-$discount_amount).'    ',0,0,'R');

        $this->pdf->SetFont('Arial','B',10);
        if(strlen($this->notice)>0) {
            $this->pdf->ln(8);

            $notice =$this->notice;
            $this->pdf->Cell(1);
            $this->pdf->MultiCell(190,6,'NOTICE: '.$notice,0,'L',0);
            $this->pdf->Cell(50);
        }
        if(strlen($this->special_info)>0) {
            $this->pdf->ln();

            $special_info =$this->special_info;
            $this->pdf->Cell(1);
            $this->pdf->MultiCell(190,6,'SPECIALS: '.$special_info,0,'L',0);
            $this->pdf->Cell(50);
        }
        $this->pdf->ln();
       // $this->pdf->SetFont('Arial','B',9);
        $reportSubtitle ='NO CLAIMS RECOGNISED UNLESS RECEIVED WITHIN 24 HOURS OF DELIVERY . ';
        $this->pdf->Cell(1);
        $this->pdf->MultiCell(110,6,$reportSubtitle,0,'L',0);
        $this->pdf->Cell(50);



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


        if($this->order['xero_invoice_id']){
            $this->pdf->setInvoiceId($this->order['xero_invoice_id']);
        }else{
            $this->pdf->setInvoiceId('INV-C'.$this->order['id']);
        }



        $this->pdf->setDate(date('Y-m-d ',$this->order['logistic_delivery_date']));
        $this->pdf->setUserName($this->userABN['business_name']);
        $this->pdf->setFactoryAccount($this->factoryAccount);
        $this->pdf->setTotalAmount($this->totalAmount);
        $this->pdf->setaccountPayment_period($this->accountPayment_period);
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
                $item_name .=' '.$item['guige_des'];

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
            $result['total_with_gst'] = round($unitPrice * $quantity,2);
            $result['total_no_gst'] = round($unitPrice * $quantity,2);
            $result['total_gst'] =  0;
            $result['gst%'] = '';
        }

        return $result;
    }

    function displayAmount($amount)
    {
        return '$'.sprintf("%1\$.2f", $amount);
    }
}

?>

