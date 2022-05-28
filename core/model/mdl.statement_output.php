<?php
// download fpdf class (http://fpdf.org)
require(DOC_DIR."static/fpdf/chinese.php");
//require('chinese.php');
define('eol', PHP_EOL);

class mdl_statement_output
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

    private  $StatementDetailsData;

    private  $StatementData;


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

    public  function setStatementDetailsData($StatementDetailsData)
      {
          $this->StatementDetailsData = $StatementDetailsData;

      }

     public function setStatementData($StatementData)
      {
          $this->StatementData = $StatementData;

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
       // $this->SetY(-40);
        $this->SetX(75);
        $this->SetFont('Arial','B',24);
        $this->Cell(50,10, 'Statement',0,1,'R');
        $this->Ln();



        if ($this->logoPath) {
            $this->Image($this->logoPath, 6, 27, 20);
        }


        $this->SetX(30);
        $this->SetFont('Arial','B',11);
        $this->Cell(50,4, $this->businessname);
        $this->SetX(150);
        $this->Cell(50,4,$this->StatementData['customer_business_name'],0,0,'R');
      //  var_dump($this->StatementData);exit;

        $this->Ln(5);
        $this->SetX(30);
        $this->SetFont('Arial','',10);
        $this->Cell(60,4,'A.B.N '.$this->abn.'  ' .'Tel: '.$this->phone.'  Fax:',0,0,'L');
        $this->SetX(150);
        $this->Cell(50,4,$this->StatementData['customer_legal_name'],0,0,'R');


        $this->Ln(5);
        $this->SetX(30);
        $this->Cell(60,4,'LINCENSE NO: ',0,0,'L');
        $this->SetX(120);
        $this->Cell(50,4,'Att:',0,0,'R');
        $this->SetX(150);
        $this->Cell(50,4,$this->StatementData['customer_contact_name'],0,0,'R');

        //分离地址
        $space_count = ceil(substr_count($this->StatementData['customer_address'],' ')/2);
        $address  = $this->StatementData['customer_address'];
        $pos = -1;
        do{
            $pos = strpos($address, ' ', $pos+1);
            $n++;
            if($n==$space_count){

                break;
            }
        }while($pos!==false);

         $address1 = substr($address,0,$pos);
        $address2 = substr($address,$pos+1);

     //   var_dump($address1 .'1e1'.$address2);exit;

        $this->Ln(5);
        $this->SetX(30);
        $this->Cell(60,4,$this->StatementData['factory_mail_address'],0,0,'L');
        $this->cell(150);
        $this->Cell(0,4,$address1,0,0,'R');


        $this->Ln(5);

        $this->cell(150);
        $this->Cell(0,4,$address2,0,0,'R');





    }

    function Footer()
    {
        $this->SetY(-40);
        $this->row("", 1, 1, 'C', 0.1);

        $this->row(" ", 0.7, 0, "L", 6);

        $this->row("", 0.3, 0, "L", 6);

        $this->ln();
        $this->row("please contact Business if you the statement not match your record.", 0.7, 0, "L", 6);
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

class customer_statement
{
    public $user;

    public $userABN;

    public $factory;

    public $factoryAccount;

    public $factoryABN;

    public $StatementData;

    public $items;

    public $itemsNotYetDue;

    public $title;//标题

    function __construct($StatementData, $items)
    {
        $this->pdf = new pdfGenerator();
        $this->StatementData = $StatementData;
        $this->items = $items;
        $this->itemsNotYetDue = $itemsNotYetDue;

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

    public  function setStatementDetailsData($StatementDetailsData)
    {
        $this->StatementDetailsData = $StatementDetailsData;

    }

    public function setStatementData($StatementData)
    {
        $this->StatementData = $StatementData;

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


    function generatePDF()
    {
        $this->pdf->setTitle($this->factoryABN['untity_name'],'2020-12-11');
        $this->pdf->setLogo(DOC_DIR.$this->logoPath);
        $this->pdf->setBusinessName($this->factoryABN['untity_name']);
        $this->pdf->setStatementData($this->StatementData);
        $this->pdf->setStatementDetailsData($this->items);

        $this->pdf->setABN($this->factoryABN['ABNorACN']);
        $this->pdf->setPhone($this->factory['phone']);
        $this->pdf->setBusinessId($this->factory['id']);

        $this->pdf->setDate(date('Y-m-d ',$this->StatementData['gen_date']));
        $this->pdf->setUserName($this->userABN['business_name']);
        $this->pdf->setFactoryAccount($this->factoryAccount);

        $this->pdf->setUser_Code($this->user_Code);
        $this->pdf->setUser($this->user, $this->userABN);

        //var_dump($this->userABN);exit;

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();
        $this->pdf->ln(5);



        $this->pdf->ln(5);
        $this->pdf->row("Date ", 0.15, 0, "L", 6);
        $this->pdf->row('Account Number ', 0.15, 0, "L", 6);
        $this->pdf->row('Account Name ', 0.40, 0, "L", 6);
        $this->pdf->row('Term', 0.15, 0, "L", 6);

        $this->pdf->row('Opening Balance', 0.15, 0, "L", 6);

        if($this->pdf->user_code['account_type']=='COD') {
            $account_type = $this->pdf->user_code['account_type'];
        }else{
            $account_type = (int)$this->pdf->user_code['account_type']*7 .'D';
        }

        $this->pdf->ln();
        $this->pdf->row(date('Y-m-d ',$this->StatementData['gen_date']), 0.15, 0, "L", 6);
        $this->pdf->row($this->StatementData['customer_id'], 0.15, 0, "L", 6);
        $this->pdf->row($this->StatementData['customer_business_name'], 0.40, 0, "L", 6);
        $this->pdf->row($account_type, 0.15, 0, "L", 6);
        $this->pdf->setFontSize(11);
        $this->pdf->row(" $ ".$this->StatementData['open_balance_amount'], 0.15, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->ln();
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->ln();


        $this->pdf->ln(5);
        $this->pdf->row('Ref Id', 0.10, 0, "l", 6);
        $this->pdf->row("Date ", 0.10, 0, "L", 6);
        $this->pdf->row('Description ', 0.2, 0, "L", 6);
        $this->pdf->row('Your Reference ', 0.15, 0, "R", 6);

        $this->pdf->row('Debit', 0.10, 0, "R", 6);
        $this->pdf->row('Credit', 0.10, 0, "R", 6);
        $this->pdf->row('Balance', 0.10, 0, "R", 6);
        $this->pdf->row('Due Date', 0.15, 0, "R", 6);


         if($this->items ) {


             foreach ($this->items as $item) {
                if($item['debit_amount']<=0  && abs($item['credit_amount']<=0)) continue;

                 $customer_ref =$item['customer_ref_id'];
                 if(!$customer_ref){
                     $customer_ref='-';
                 }else{

                 }
                 $this->pdf->ln();
                 $this->pdf->row($item['id'], 0.10, 0, "L", 6);
                 $this->pdf->row(date('Y-m-d ', $item['gen_date']), 0.10, 0, "L", 6);
                 $this->pdf->row(strtoupper($item['code_desc_en']), 0.2, 0, "L", 6);
                 $this->pdf->row(strtoupper($customer_ref), 0.15, 0, "R", 6);


                 $this->pdf->row(number_format($item['debit_amount'],2), 0.10, 0, "R", 6);

                 $this->pdf->row(number_format($item['credit_amount'],2), 0.10, 0, "R", 6);


                 $this->pdf->row(number_format($item['balance_due'],2), 0.10, 0, "R", 6);
                 if(!$item['overdue_date']) {
                     $this->pdf->row('-', 0.10, 0, "R", 6);
                 }else{
                     $this->pdf->row(date('Y-m-d ', $item['overdue_date']), 0.15, 0, "R", 6);
                 }



             }

             $this->pdf->ln(7);
             $this->pdf->row("", 1, 1, 'C', 0.1);
             $this->pdf->ln(5);
             $this->pdf->setFontSize(11);

             if($this->StatementData['not_due_amount']<0){
                 $not_over_due =0.00;
             }else{
                 $not_over_due =number_format($this->StatementData['not_due_amount'],2);
             }

             if($this->StatementData['close_balance_amount']<0){
                 $close_balance_amount ='(We Owe You)   $'.number_format($this->StatementData['close_balance_amount']*-1,2);
             }else{
                 $close_balance_amount ='$'.number_format($this->StatementData['close_balance_amount'],2);
             }

             $this->pdf->row('Not Over Due: $'.$not_over_due, 0.3, 0, "L", 6);
             $this->pdf->row(' Over Due: $'.number_format($this->StatementData['overdue_amount'],2), 0.3, 0, "L", 6);
             $this->pdf->row('Close Balance: '.$close_balance_amount, 0.3, 0, "R", 6);
             $this->pdf->row('AUD', 0.1, 0, "L", 6);

             $this->pdf->setFontSize();

             $this->pdf->ln(6);

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

