<?php
// download fpdf class (http://fpdf.org)
require(DOC_DIR."static/fpdf/chinese.php");
//require('chinese.php');
define('eol', PHP_EOL);

class  mdl_invoice
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

    public function setLogo($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    function Header()
    {
        // Logo
        //var_dump($this->logoPath);exit;
        if ($this->logoPath) {
            $this->Image($this->logoPath, 10, 6, 30);
        }
        $this->setFontSize(18);
        // Title
        $this->row('', 0.1, 0);
        $this->row($this->fileTitle, 0.6, 0, 'C');
        $this->setFontSize();
        // $this->row($this->headerTag, 0.3, 0, 'C');

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

/**
 *  资金流水PDF
 */
class AccountTransactionReport
{
    public $title;

    public $from;

    public $to;

    public $transactionData;

    private $pdf; //array

    private $dataSet1;

    private $dataSet2;

    private $dataSet3;

    private $dataSet4;

    function __construct()
    {
        $this->pdf = new pdfGenerator();
    }

    function title($value = '', $headerTag = '')
    {
        if (! $headerTag) {
            $headerTag = $this->from." To ".$this->to;
        }

        $this->pdf->setTitle($value, $headerTag);

        return $this;
    }

    function from($value = '')
    {
        $this->from = $value;

        return $this;
    }

    function to($value = '')
    {
        $this->to = $value;

        return $this;
    }

    function transactionData($value = '')
    {
        $this->transactionData = $value;

        return $this;
    }

    function dataSet1($value = '')
    {
        $this->dataSet1 = $value;

        return $this;
    }

    function dataSet2($value = '')
    {
        $this->dataSet2 = $value;

        return $this;
    }

    function dataSet3($value = '')
    {
        $this->dataSet3 = $value;

        return $this;
    }

    function dataSet4($value = '')
    {
        $this->dataSet4 = $value;

        return $this;
    }

    private function status($statusNo)
    {
        switch ($statusNo) {
            case 0:
                $str = "Processing";
                break;
            case 1:
                $str = "settled";
                break;
            case 2:
                $str = "cancelled";
                break;
            case 3:
                $str = "no process yet";
                break;
            default:
                $str = "not defined";
                break;
        }

        return $str;
    }

    function generatePDF($thisLang)
    {
        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo.png');
        $this->pdf->AddPage();
        $this->pdf->SetLeftMargin(10);

        $this->pdf->row("Order ID", 0.22, 1, 'C');
        $this->pdf->row("说明", 0.5, 1, 'C');
        $this->pdf->row("金额", 0.1, 1, 'C');
        $this->pdf->row("提交时间", 0.1, 1, 'C');
        $this->pdf->row("状态", 0.08, 1, 'C');
        $this->pdf->ln();

        for ($i = 0; $i < sizeof($this->transactionData); $i++) {
            $this->pdf->row($this->transactionData[$i]['orderId'], 0.22, 1);
            $this->pdf->row($this->transactionData[$i]['coupon_name'], 0.5, 1);
            $this->pdf->row($this->transactionData[$i]['money'], 0.1, 1);
            $this->pdf->row(date("m-d H:i", $this->transactionData[$i]['createTime']), 0.1, 1);
            $this->pdf->row($this->status($this->transactionData[$i]['status']), 0.08, 1);
            $this->pdf->ln();
        }

        $this->pdf->AddPage();

        $lsf = 20; //left shift;
        $this->pdf->myText($lsf + 30, 30, '总营收:');
        $this->pdf->myText($lsf + 120, 30, $this->dataSet1['totalIn']);

        $this->pdf->myText($lsf + 40, 35, '总商品销售:');
        $this->pdf->myText($lsf + 120, 35, $this->dataSet2['totalGoodsSales']);
        $this->pdf->myText($lsf + 50, 40, '使用折扣码:');
        $this->pdf->myText($lsf + 120, 40, $this->dataSet2['promotionTotal']);
        $this->pdf->myText($lsf + 50, 45, '运费:');
        $this->pdf->myText($lsf + 120, 45, $this->dataSet2['deliveryFee']);
        $this->pdf->myText($lsf + 50, 50, '平台预订费代收:');
        $this->pdf->myText($lsf + 120, 50, $this->dataSet2['platformFee']);
        $this->pdf->myText($lsf + 50, 55, '钱包支付:');
        $this->pdf->myText($lsf + 120, 55, $this->dataSet2['useMoney']);
        $this->pdf->myText($lsf + 50, 60, '三方支付手续费:');
        $this->pdf->myText($lsf + 120, 60, $this->dataSet2['transactionFee']);

        $this->pdf->myText($lsf + 40, 70, '总交易额:');
        $this->pdf->myText($lsf + 120, 70, $this->dataSet2['transactionBalance']);
        $this->pdf->myText($lsf + 50, 75, 'Paypal支付:');
        $this->pdf->myText($lsf + 120, 75, $this->dataSet3['paypal']);
        $this->pdf->myText($lsf + 50, 80, 'Wechat支付:');
        $this->pdf->myText($lsf + 120, 80, $this->dataSet3['royalpay']);
        $this->pdf->myText($lsf + 50, 85, 'Alipay支付:');
        $this->pdf->myText($lsf + 120, 85, $this->dataSet3['alipay']);
        $this->pdf->myText($lsf + 50, 90, 'Credit Card支付:');
        $this->pdf->myText($lsf + 120, 90, $this->dataSet3['creditcard']);
        $this->pdf->myText($lsf + 50, 95, 'Hcash支付:');
        $this->pdf->myText($lsf + 120, 95, $this->dataSet3['hcash']);
        $this->pdf->myText($lsf + 50, 100, '线下支付:');
        $this->pdf->myText($lsf + 120, 100, $this->dataSet3['offline']);

        $this->pdf->myText($lsf + 40, 110, '线上交易总额:');
        $this->pdf->myText($lsf + 120, 110, $this->dataSet3['transactionBalanceOnline']);
        $this->pdf->myText($lsf + 50, 115, '商家自收款:');
        $this->pdf->myText($lsf + 120, 115, $this->dataSet1['TRANSACTION_BALANCE'] - $this->dataSet3['transactionBalanceOnline']);
        $this->pdf->myText($lsf + 50, 120, '平台折扣码返还:');
        $this->pdf->myText($lsf + 120, 120, $this->dataSet1['PROMOTION_REFOUND']);
        $this->pdf->myText($lsf + 50, 125, '平台补贴三方手续费:');
        $this->pdf->myText($lsf + 120, 125, $this->dataSet1['TRANSACTION_FEE_PLATFORM_REFOUND']);

        $this->pdf->myText($lsf + 40, 135, '钱包支付进账:');
        $this->pdf->myText($lsf + 120, 135, $this->dataSet1['MONEYPAY_BALANCETRANSFER']);

        $this->pdf->myText($lsf + 30, 145, '总支出:');
        $this->pdf->myText($lsf + 120, 145, $this->dataSet1['totalOut']);
        //	$this->pdf->myText($lsf+40, 150 , '平台交易手续费:');$this->pdf->myText($lsf+120, 150 ,  $this->dataSet1['UBONUS_COMMISSION']);
        $this->pdf->myText($lsf + 40, 150, '平台交易手续费(EXGST:'.$this->dataSet2['commissionOfsaleswithoutGst'].'/GST:'.$this->dataSet2['commissionOfsalesGst'].'):');
        $this->pdf->myText($lsf + 120, 150, $this->dataSet2['commissionOfsales']);

        $this->pdf->myText($lsf + 40, 155, '平台预订费返还:');
        $this->pdf->myText($lsf + 120, 155, $this->dataSet1['PLATFORM_FEE']);
        $this->pdf->myText($lsf + 40, 160, '平台收取支付手续费:');
        $this->pdf->myText($lsf + 120, 160, $this->dataSet1['TRANSACTION_FEE_PLATFORM_CHARGE']);

        $this->pdf->myText($lsf + 30, 170, '其它:');
        $this->pdf->myText($lsf + 120, 170, $this->dataSet4['personalTransactionTotal']);
        $this->pdf->myText($lsf + 40, 175, '个人取现:');
        $this->pdf->myText($lsf + 120, 175, $this->dataSet4['withdraw']);
        $this->pdf->myText($lsf + 40, 180, '红包:');
        $this->pdf->myText($lsf + 120, 180, $this->dataSet4['redbag']);
        $this->pdf->myText($lsf + 40, 185, '使用钱包:');
        $this->pdf->myText($lsf + 120, 185, $this->dataSet4['usemoneypay']);
        $this->pdf->myText($lsf + 40, 190, '用户介绍费:');
        $this->pdf->myText($lsf + 120, 190, $this->dataSet4['CUSTOMER_REF_COMMISSION']);
        $this->pdf->myText($lsf + 40, 195, '商家介绍费:');
        $this->pdf->myText($lsf + 120, 195, $this->dataSet4['BUSINESS_REF_COMMISSION']);

        $this->pdf->myText($lsf + 30, 205, 'Balance:');
        $this->pdf->myText($lsf + 120, 205, $this->dataSet1['totalBalance']);
    }

    function outPutToFile($savePdfName, $dest)
    {
        $this->pdf->Output($savePdfName, $dest);
    }

    function outPutToBrowser($savePdfName)
    {

        $this->pdf->Output($savePdfName, "I");
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

    function setCustomer_delivery_date($customer_delivery_date)
    {
        $this->customer_delivery_date = $customer_delivery_date;

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

    function OrderData($value = '')
    {
        $this->OrderData = $value;

        return $this;
    }

    function generatePDF($thisLang)
    {
        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo.png');

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        $total = 0;

        $this->pdf->row("Order ID", 0.2, 0, 'C', 12);
        $this->pdf->row($thisLang->customer_name, 0.25, 0, 'C', 12);
        $this->pdf->row($thisLang->telephone, 0.25, 0, 'C', 12);
        $this->pdf->row($thisLang->payment_option, 0.1, 0, 'C', 12);
        $this->pdf->row($thisLang->ifpaid, 0.1, 0, 'C', 12);
        $this->pdf->row($thisLang->deliver_option, 0.1, 0, 'C', 12);
        $this->pdf->ln(10);

        foreach ($this->OrderData as $key => $order) {
            //orderId//first_name+last_name//phone//status//payment//customer_delivery_option

            $name =$order['name'];

			 $this->pdf->row('Cust Id:'.$order['userId'], 0.15, 0, 'C');
            $this->pdf->row('Cust Name:'.$name, 0.7, 0, 'C');
			 $this->pdf->ln();
			
		    $orderID = $order['orderId'];
            $this->pdf->row($orderID, 0.45, 1);

           

            $this->pdf->row($order['phone'], 0.25, 1, 'C');

            $payment = $order['payment'];
            $this->pdf->row($payment, 0.1, 1, 'C');

            $status = $order['status'];
            $status = ($status == 0) ? $thisLang->unpaid : $thisLang->paid;
            $this->pdf->row($status, 0.1, 1, 'C');

            $customer_delivery_option = $order['customer_delivery_option'];
            if ($customer_delivery_option == '1') {
                $customer_delivery_option = $thisLang->logistic_delivery_busi;
            } elseif ($customer_delivery_option == '2') {
                $customer_delivery_option = $thisLang->logistic_delivery_pickup;
            } else {
                $customer_delivery_option = $thisLang->no_need_delivery;
            }

            $this->pdf->row($customer_delivery_option, 0.1, 1, 'C');

            $this->pdf->ln(10);

            foreach ($order['items'] as $item) {
                //bonus_title(guige_des)//voucher_deal_amount//customer_buying_quantity

                $this->pdf->row('ITEM:'.$item['id'], 0.2, 0, 'C');
                $title = mb_substr($item['bonus_title'], 0, 50)." ".$item['guige_des'];
                $this->pdf->row($title, 0.6, 0, 'L');
                $this->pdf->row('$'.$item['voucher_deal_amount'], 0.1, 0, 'C');
                $this->pdf->row('x'.$item['customer_buying_quantity'], 0.1, 0, 'C');
                $this->pdf->ln();

                $total += $item['voucher_deal_amount'] * $item['customer_buying_quantity'];
            }
            $this->pdf->ln(10);
        }

        $this->pdf->AddPage();

        $this->pdf->myText(130, 30, $thisLang->start_time.': ');
        $this->pdf->myText(168, 30, $this->startTime);
        $this->pdf->myText(130, 35, $thisLang->end_time.': ');
        $this->pdf->myText(168, 35, $this->endTime);
        $this->pdf->myText(130, 45, $thisLang->salesTotal.': ');
        $this->pdf->myText(178, 45, "$".$total);
    }

    function generatePDF_logistic_list($thisLang)
    {
        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo.png');

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        $total = 0;

        foreach ($this->OrderData as $key => $order) {
            //orderId//first_name+last_name//phone//status//payment//customer_delivery_option
            $this->pdf->row("Order ID", 0.2, 0, 'C', 12);
            $this->pdf->row('Customer Name', 0.35, 0, 'C', 12);
            $this->pdf->row('Phone', 0.15, 0, 'C', 12);
            $this->pdf->row('Payment', 0.1, 0, 'C', 12);
            $this->pdf->row('Status', 0.1, 0, 'C', 12);
            $this->pdf->row('Delivery Op', 0.1, 0, 'C', 12);
            $this->pdf->ln(10);
            $orderID = $order['orderId'];
            $this->pdf->row($orderID, 0.2, 1);

            $name =$order['name'];
		
            $this->pdf->row(substr($name,0,45), 0.35, 1, 'C');

            $this->pdf->row($order['phone'], 0.15, 1, 'C');

            $payment = $order['payment'];
            $this->pdf->row($payment, 0.1, 1, 'C');

            $status = $order['status'];
            $status = ($status == 0) ? $thisLang->unpaid : $thisLang->paid;
            $this->pdf->row($status, 0.1, 1, 'C');

            $customer_delivery_option = $order['customer_delivery_option'];
            if ($customer_delivery_option == '1') {
                $customer_delivery_option = $thisLang->logistic_delivery_busi;
            } elseif ($customer_delivery_option == '2') {
                $customer_delivery_option = $thisLang->logistic_delivery_pickup;
            } else {
                $customer_delivery_option = $thisLang->no_need_delivery;
            }

            $this->pdf->row($customer_delivery_option, 0.1, 1, 'C');

            $this->pdf->ln(10);
            $this->pdf->setFontSize(18);
            $this->pdf->row('Delivery Date', 0.3, 0, 'C', 12);
            $this->pdf->row('SequenceNo', 0.3, 0, 'C', 12);
            $this->pdf->row('truckNo', 0.2, 0, 'C', 12);
            $this->pdf->row('stopNo', 0.2, 0, 'C', 12);

            $this->pdf->setFontSize();

            $this->pdf->ln(10);

            $logistic_truck_No = $order['logistic_truck_No'];
            $logistic_delivery_date = date('Y-m-d', $order['logistic_delivery_date']);
            $logistic_stop_No = $order['logistic_stop_No'];
            $logistic_sequence_No = $order['logistic_sequence_No'];
            $this->pdf->setFontSize(18);
            $this->pdf->row($logistic_delivery_date, 0.3, 1, "C", 15);
            $this->pdf->setFontSize(40);
            $this->pdf->row($logistic_sequence_No, 0.3, 1, "C", 15);
            $this->pdf->setFontSize(18);
            $this->pdf->row('D'.$logistic_truck_No, 0.20, 1, "C", 15);
            $this->pdf->row('S'.$logistic_stop_No, 0.20, 1, "C", 15);

            $this->pdf->setFontSize();
            $this->pdf->ln(20);

            $this->pdf->setFontSize(18);
            $this->pdf->row('Suppliers Count ', 0.3, 0, 'C', 12);
            $this->pdf->row('Suppliers name ', 0.7, 0, 'C', 12);
            $this->pdf->setFontSize();
            $logistic_suppliers_count = $order['logistic_suppliers_count'];
            $logistic_suppliers_info = $order['logistic_suppliers_info'];
            $this->pdf->ln(10);
            $this->pdf->setFontSize(24);
            $this->pdf->row($logistic_suppliers_count, 0.3, 1, "C", 15);
            $this->pdf->setFontSize(12);
            $this->pdf->row($logistic_suppliers_info, 0.7, 1, "C", 15);

            $this->pdf->setFontSize();
            $this->pdf->ln(20);

            foreach ($order['items'] as $item) {
                //bonus_title(guige_des)//voucher_deal_amount//customer_buying_quantity

                $this->pdf->row('ITEM:'.$item['id'], 0.2, 0, 'C');
                $title = mb_substr($item['bonus_title'], 0, 60)." ".$item['guige_des'];
                $this->pdf->row($title, 0.6, 0, 'L');
                $this->pdf->row('$'.$item['voucher_deal_amount'], 0.1, 0, 'C');
                $this->pdf->row('x'.$item['customer_buying_quantity'], 0.1, 0, 'C');
                $this->pdf->ln();

                $total += $item['voucher_deal_amount'] * $item['customer_buying_quantity'];
            }
            $this->pdf->ln(10);
        }

        $this->pdf->AddPage();

        $this->pdf->myText(130, 30, $thisLang->start_time.': ');
        $this->pdf->myText(168, 30, $this->startTime);
        $this->pdf->myText(130, 35, $thisLang->end_time.': ');
        $this->pdf->myText(168, 35, $this->customer_delivery_date);
        $this->pdf->myText(130, 45, $thisLang->salesTotal.': ');
        $this->pdf->myText(178, 45, "$".$total);
    }
	




    function findCustomerName($userId){



		//首先找displayName 
		$user =loadModel('user')->get($userId) ;
		if($user['displayName']) {
			return $user['displayName'];
		} else{
			
			$name = $user['name'];
		}
			
		//之后找 user.name  user_factory.nickname 
		$where =array (
		  'user_id'=>$userId 
		
		);
		$user1 = loadModel('user_factory')->getByWhere($where);
		if($user1) {
			if($user1['nickname']) {
				return '('.$user1['nickname'].')'.$name;
			}else{
				
				return $name;
			}
			
			
		}else{
			
			return $name;
		}
		
		
		
		
		
	}

    function generatePDF_order_amend()
    {
        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo.png');

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        $total = 0;

        $this->pdf->row('订单号', 0.2, 0, 'C', 12);
        $this->pdf->row('客户姓名', 0.25, 0, 'C', 12);
        $this->pdf->row('电话', 0.25, 0, 'C', 12);
        $this->pdf->row('原价格', 0.1, 0, 'C', 12);
        $this->pdf->row('新价格', 0.1, 0, 'C', 12);
        $this->pdf->row('差额', 0.1, 0, 'C', 12);
        $this->pdf->ln(10);

        foreach ($this->OrderData as $key => $order) {
            //orderId//first_name+last_name//phone//status//payment//customer_delivery_option

            $orderID = $order['orderId'];
            $this->pdf->row($orderID, 0.2, 1);

            $name =$order['name'];
            $this->pdf->row($name, 0.25, 1, 'C');

            $this->pdf->row($order['phone'], 0.25, 1, 'C');

            $old_sub_total = $order['old_sub_total'];
            $this->pdf->row($old_sub_total, 0.1, 1, 'C');

            $new_sub_total = $order['new_sub_total'];

            $this->pdf->row($new_sub_total, 0.1, 1, 'C');

            $amend = $order['amend'];
            $this->pdf->row($amend, 0.1, 1, 'C');

            $this->pdf->ln(10);

            $this->pdf->row('Claim Item', 0.3, 0, 'C', 12);
            $this->pdf->row('Reason', 0.2, 0, 'C', 12);
            $this->pdf->row('Create Time', 0.2, 0, 'C', 12);
            $this->pdf->row('Message', 0.3, 0, 'C', 12);

            $this->pdf->ln(10);

            $bonus_title = $order['bonus_title'];
            $this->pdf->row($bonus_title, 0.3, 1);

            $reason_type = $order['reason_type'];

            $reason_type_rec = loadModel('order_amend_reson_type')->get($reason_type);
            $reason_type_desc = $reason_type_rec['reson_type_cn'];

            $this->pdf->row($reason_type_desc, 0.2, 1, 'C');

            $this->pdf->row(date("Y-m-d", $order['amend_time']), 0.2, 1, 'C');

            $message = $order['message'];
            $this->pdf->row($message, 0.3, 1, '0');
            $total += $amend;
            $this->pdf->ln(30);
        }

        $this->pdf->AddPage();

        $this->pdf->myText(130, 30, 'Start Date:');
        $this->pdf->myText(168, 30, $this->startTime);
        $this->pdf->myText(130, 35, $thisLang->end_time.': ');
        $this->pdf->myText(168, 35, $this->endTime);
        $this->pdf->myText(130, 45, "Total Amount：");
        $this->pdf->myText(178, 45, "$".$total);
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

    public function findCustomerName($userId){
	  //return 'exmaple'; 
		
		//首先找displayName 
		$user =loadModel('user')->get($userId) ;
		if($user['displayName']) {
			return $user['displayName'];
		} else{
			
			$name = $user['name'];
		}
			
		//之后找 user.name  user_factory.nickname 
		$where =array (
		  'user_id'=>$userId 
		
		);
		$user1 = loadModel('user_factory')->getByWhere($where);
		if($user1) {
			if($user1['nickname']) {
				return '('.$user1['nickname'].')'.$name;
			}else{
				
				return $name;
			}
			
			
		}else{
			
			return $name;
		}
		
		
		
		
		
	}


    function generatePDF($thisLang)
    {
        $this->pdf->setLogo(DOC_DIR.$this->logoPath);

        $this->pdf->SetLeftMargin(10);
        $this->pdf->AddPage();

        foreach ($this->OrderData as $key => $order) {

       //    $this->pdf->Image($order['redeemQRCode'], 181, 22, 20);
			$this->pdf->ln();
            $orderID = "ORDER ID: ".$order['orderId'];
            $this->pdf->row($orderID, 0.4, 0, "L", 6);
	       $this->pdf->row("Cust ID: ".$order['userId'], 0.4, 0, "L", 6);
          

            $phone = "PHONE: ".$order['phone'];
            $this->pdf->row($phone, 0.2, 0, "L", 6);

            $this->pdf->ln(10);
            $this->pdf->row("NAME: ", 0.1, 0, "L", 6);
            $name =$order['name'];
            $this->pdf->setFontSize(16);
            $this->pdf->row(substr($name,0,50), 0.5, 0, "L", 6);
            $this->pdf->setFontSize();
          //  
		
			$this->pdf->row('', 0.2, 0, 'L', 6);


            $payment = $order['payment'];

            $status = $order['status'];
            $status = ($status == 0) ? $thisLang->unpaid : $thisLang->paid;

            $customer_delivery_option = $order['customer_delivery_option'];
            if ($customer_delivery_option == '1') {
                $customer_delivery_option = $thisLang->logistic_delivery_busi;
            } elseif ($customer_delivery_option == '2') {
                $customer_delivery_option = $thisLang->logistic_delivery_pickup;
            } else {
                $customer_delivery_option = $thisLang->no_need_delivery;
            }

            $this->pdf->row($payment."|".$status."|".$customer_delivery_option, 0.3, 0, "L", 6);
				 $this->pdf->ln(8);
            if($order['message_to_business']) {
			
			
			$this->pdf->row('Message'.$order['message_to_business'], 1, 0, 'L', 6);
			}
           
			
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
           
          //  $this->pdf->row('Suppliers name ', 1, 0, 'C', 12);
         //   $this->pdf->setFontSize();
           
		   
		   
		   
		   
		 //  $this->pdf->ln(20);
				
				$this->pdf->row("Suppliers info:", 0.2, 0, "C", 12);
			   
			
				$logistic_suppliers_info = $order['logistic_suppliers_info'];
				$this->pdf->setFontSize(12);
				$this->pdf->row($logistic_suppliers_info, 0.8, 1, "C", 15);
				$this->pdf->setFontSize();
		   
		   
		   
		   
		   
		   
		   
		   
		   
          
           
           // $this->pdf->setFontSize(12);
           // $this->pdf->row($logistic_suppliers_info, 1, 1, "C", 15);

           // $this->pdf->setFontSize();
            $this->pdf->ln(20);

            foreach ($order['items'] as $item) {
                $this->pdf->row("", 0.05);
                $this->pdf->setFontSize(14);
				
				$title1 =$item['bonus_title'].' '.$item['guige_des'];
				$title_length=strlen($title1);
				$count =ceil($title_length/60);
				
				if($count ==1 ){
					
					 $title = mb_substr($title1, 0, 60);
					 $this->pdf->row('[  ]  '.$title.'(Qty X'.$item['customer_buying_quantity'].')', 0.85, 0, 'L');
					 $this->pdf->setFontSize(16);
					 $this->pdf->row('x'.$item['customer_buying_quantity'], 0.1, 0, 'C');

					 $this->pdf->ln();
					 $this->pdf->setFontSize();
					
				}else{
					
					
					for($i=0;$i<$count;$i++) {
						
					
						
					  if ($count==(1+$i)) {
						    $title = substr($title1, $i*60, ($i+1)*60);
							$this->pdf->row($title.'(Qty X'.$item['customer_buying_quantity'].')', 0.80, 0, 'L');
							$this->pdf->setFontSize(16);
							$this->pdf->row('x'.$item['customer_buying_quantity'], 0.15, 0, 'C');

							$this->pdf->ln();
						
						  
						  
					  }else{
						  $title = substr($title1, $i*60, ($i+1)*60);
						  $this->pdf->row('[  ]  '.$title, 0.8, 0, 'L');
						  $this->pdf->ln(8);
						 
					  }
					
					
				    }
					
					
				}
                if($item['message']) {

                    $this->pdf->row('Customer Notes:' .$item['message'], 1, 0, 'C');
                    $this->pdf->row("", 1, 1, 'C', 0.1);
                    $this->pdf->ln();

                }
				 $this->pdf->setFontSize();
               
            }

            foreach ($order['hasLottery'] as $item) {
                $this->pdf->row("", 0.05);

                $title = "奖品合单：".$item['lottery_sub_name'].",".$item['lottery_sub_details'];
                $this->pdf->row(trim($title), 0.85, 0, 'L');
                $this->pdf->row($item['lid']."-".$item['lcid'], 0.1, 0, 'C');
                $this->pdf->ln();
            }

         /*   if ($this->returnAddress) {
                $this->pdf->setFontSize(7);
                $this->pdf->row("If not delivered please return to: ".$this->returnAddress." ", 1, 0, 'C', 8);
                $this->pdf->setFontSize();
            } */

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

/*
 * Class 订单流水PDF
 */

class settlementReport
{
    public $title;//标题

    public $OrderData;

    public $startTime;

    public $endTime;

    public $returnAddress;

    private $fitInPage;

    function __construct()
    {
        $this->pdf = new pdfGenerator();
        $this->fitInPage = false;
    }

    function setStarttime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    function setBSB($bsb)
    {
        $this->bsb = $bsb;

        return $this;
    }

    function setAccountNumber1($accountNumber1)
    {
        $this->accountNumber1 = $accountNumber1;

        return $this;
    }

    function transactionData($value = '')
    {
        $this->transactionData = $value;

        return $this;
    }

    function dataSet1($value = '')
    {
        $this->dataSet1 = $value;

        return $this;
    }

    function dataSet2($value = '')
    {
        $this->dataSet2 = $value;

        return $this;
    }

    function dataSet3($value = '')
    {
        $this->dataSet3 = $value;

        return $this;
    }

    function dataSet4($value = '')
    {
        $this->dataSet4 = $value;

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
            $headerTag = "From ".$this->startTime." To ".$this->endTime;
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

    function setBusinessName($businessName)
    {
        $this->businessName = $businessName;

        return $this;
    }

    function setABN($abn)
    {
        $this->abn = $abn;

        return $this;
    }

    function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    function setCommissionRate($commissionRate)
    {
        $this->commissionRate = $commissionRate;

        return $this;
    }

    function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    function setRefNumber($refNumber)
    {
        $this->refNumber = $refNumber;

        return $this;
    }

    function generatePDF($thisLang)
    {
        $this->pdf->setLogo('');

        $this->pdf->SetLeftMargin(10);

        $this->pdf->AddPage();
        $business_name = $this->businessName;
        $account_name = $this->tradingName;
        $abn = $this->abn;
        $account_number = $this->accountNumber;
        $ref_number = $this->refNumber;

        $this->pdf->ln();

        $this->pdf->row("Business Name: ".$business_name, 0.7, 0, "L", 6);
        $this->pdf->row("Account Number: ".$account_number, 0.3, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Trading Name: ".$account_name, 0.7, 0, "L", 6);
        $this->pdf->row("RefNumber: ".$ref_number, 0.2, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("ABN/ACN: ".$abn, 0.7, 0, "L", 6);
        $this->pdf->row("From: ".$this->startTime.' to '.$this->endTime, 0.1, 0, "L", 6);
        $this->pdf->ln(10);
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->ln(5);

        $totalIncGST = "Total Incl GST ";
        $exGST = "Ex GST";
        $gst = "GST";

        $totalAmountofTranscations = $this->dataSet1['totalIn'];
        $third_party_surcharges = $this->dataSet2['transactionFee'];
        $delivery_fees = $this->dataSet2['deliveryFee'];
        $delivery_fees_exgst = $delivery_fees / 11 * 10;
        $delivery_fees_gst = floatval($delivery_fees) / 11;
        // var_dump($delivery_fees_gst);exit;
        $total_amount_of_selling = $this->dataSet2['totalGoodsSales'] + $this->dataSet2['totalamend'];
        $product_Damage_or_Missing_Without_gst = $this->dataSet2['totalamend'];
        $product_Damage_or_Missing_With_gst = $this->dataSet2['totalamend'];

        // var_dump($this->gstType);exit;

        if ($this->gstType == 2) { // 商家为不交GST商家 此部分有误 需要更改！

            $total_amount_of_selling_with_gst = $total_amount_of_selling * 0;
            $total_amount_of_selling_without_gst = $total_amount_of_selling * 1;
            $net_sales_ex_GST = $total_amount_of_selling_without_gst - $product_Damage_or_Missing_With_gst;
            $net_sales_with_GST = $total_amount_of_selling * 0;
        }

        if ($this->gstType == 1) { // 商家为交GST商家 此部分有误 需要更改！
            $total_amount_of_selling_with_gst = $total_amount_of_selling * 1;
            $total_amount_of_selling_without_gst = $total_amount_of_selling * 0;
            $net_sales_ex_GST = 0;
            $net_sales_with_GST = $total_amount_of_selling_with_gst - $product_Damage_or_Missing_Without_gst;
        }

        $net_sales_with_GST_sell = $net_sales_with_GST / 11 * 10;
        $net_sales_with_GST_sell_GST = $net_sales_with_GST / 11;
        $total_discount_code_amend = $this->dataSet2['promotionTotal'];

        $subTotal_Net_sales = $net_sales_with_GST + $net_sales_ex_GST;

        $subTotal_Net_sales_ex_gst = $net_sales_with_GST_sell + $net_sales_ex_GST;
        $subTotal_Net_sales_gst = $net_sales_with_GST_sell_GST;

        $totalPayable = $total_payable_ex_gst + $total_payable_gst;

        $total_commission_withGST_selling = $net_sales_with_GST * $this->commissionRate; //有gst 部分收取commission

        $commission_without_GST_selling = $net_sales_ex_GST * $this->commissionRate;  //无gst 收取commission
        $commission_without_GST_selling_plusGST = $commission_without_GST_selling * 0.1;///无gst 收取commission

        $total_commission = $commission_without_GST_selling + $commission_without_GST_selling_plusGST;

        $this->pdf->ln();

        $this->pdf->row(" ", 0.55, 0, "L", 6);
        $this->pdf->row($totalIncGST, 0.15, 0, "L", 6);
        $this->pdf->row($exGST, 0.15, 0, "L", 6);
        $this->pdf->row($gst, 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln(10);
        $this->pdf->row("Amount of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalAmountofTranscations), 0.15, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("DiscountCode amend: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $total_discount_code_amend), 0.15, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->setFontSize(10);
        $this->pdf->row("Amount of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalAmountofTranscations), 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln(10);
        $this->pdf->setFontSize(10);
        $this->pdf->row("Breakdown of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->ln();

        $this->pdf->row("1)Third party surcharges: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $third_party_surcharges), 0.15, 0, "L", 6);
        $this->pdf->ln();
        // $this->pdf->row("",1,1,'C',0.1);

        $this->pdf->row("2)Delivery fees: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $delivery_fees), 0.15, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $delivery_fees_exgst), 0.15, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $delivery_fees_gst), 0.15, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("3)Total amount of selling: ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $total_amount_of_selling), 0.15, 0, "L", 6);

        $this->pdf->ln(10);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Sales by Product Type : ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();

        if ($this->gstType != 2) { //此部分有误 需要更改！
            $this->pdf->ln();
            $this->pdf->row("With GST  sales (Taxable sales): ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_amount_of_selling_with_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("  Product Damage or Missing: ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $product_Damage_or_Missing_With_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("SubTotal Net sales (Taxable sales): ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $net_sales_with_GST), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $net_sales_with_GST_sell), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $net_sales_with_GST_sell_GST), 0.15, 0, "L", 6);
        }

        if ($this->gstType != 1) { //此部分有误 需要更改！
            $this->pdf->ln(10);

            $this->pdf->row("GST Free Product: ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_amount_of_selling_without_gst), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_amount_of_selling_without_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("   Product Damage or Missing: ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $product_Damage_or_Missing_Without_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->setFontSize(10);
            $this->pdf->row("SubTotal Net sales : ", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $subTotal_Net_sales), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $subTotal_Net_sales_ex_gst), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $subTotal_Net_sales_gst), 0.15, 0, "L", 6);
            $this->pdf->setFontSize();
        }

        $this->pdf->ln(10);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Commission Details (See next Page Invoice): ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();

        if ($this->gstType != 2) { //此部分有误 需要更改！

            $this->pdf->ln();
            $this->pdf->row("Commission on Taxable sales(".($this->commissionRate * 100)."% of Sales Value)", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling / 11 * 10), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling / 11), 0.15, 0, "L", 6);
        }

        if ($this->gstType != 1) { //此部分有误 需要更改！

            $this->pdf->ln();
            $this->pdf->row("Commission on GST Free Sales(".($this->commissionRate * 100)."% of Sales Value+GST)", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling * 1.1), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling * 0.1), 0.15, 0, "L", 6);
        }

        $totalCommissionIncGST = $total_commission_withGST_selling + $commission_without_GST_selling * 1.1;
        $totalCommissionIncGST_exgst = $total_commission_withGST_selling / 11 * 10 + $commission_without_GST_selling;
        $totalCommissionIncGst_gst = $total_commission_withGST_selling / 11 + $commission_without_GST_selling * 0.1;

        if (! ($this->gstType == 1 || $this->gstType == 2)) { //此部分有误 需要更改！
            $this->pdf->ln();
            $this->pdf->setFontSize();

            $this->pdf->row("Total Commission charged", 0.55, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $totalCommissionIncGST), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $totalCommissionIncGST_exgst), 0.15, 0, "L", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $totalCommissionIncGst_gst), 0.15, 0, "L", 6);
        }

        $this->pdf->ln(20);
        $this->pdf->setFontSize();
        $totalAccountBalanceIncGST = $subTotal_Net_sales + $delivery_fees - $totalCommissionIncGST;
        $totalAccountBalance_exgst = $delivery_fees_exgst + $subTotal_Net_sales_ex_gst - $totalCommissionIncGST_exgst;
        $totalAccountBalance_gst = $subTotal_Net_sales_gst + $delivery_fees_gst - $totalCommissionIncGst_gst;

        $this->pdf->row("Total Account Balance  : ", 0.55, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalAccountBalanceIncGST), 0.15, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalAccountBalance_exgst), 0.15, 0, "L", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalAccountBalance_gst), 0.15, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Balance = Netsales + Deliveryfees -Totalcommissions: ", 0.55, 0, "L", 6);

        $this->pdf->ln(30);

        $this->pdf->row("Payments will automaticlly Transfer to business Account .", 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("If can not received  ,please contact Ubonus Payment department ", 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Thank you very much for your business!", 0.7, 0, "L", 6);
        $this->pdf->ln();
        //另外一页 出commission invoice 发票

        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo-print.png');
        $this->pdf->setTitle("");
        $this->pdf->AddPage();

        $this->pdf->ln();

        $this->pdf->setFontSize(20);
        $this->pdf->row("TAX INVOICE ", 0.6, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->row("Invoice Date: ", 0.2, 0, "L", 6);
        $this->pdf->row("CityB2B  Pty Ltd", 0.2, 0, "L", 6);

        $this->pdf->ln();

        $this->pdf->row("", 0.6, 0, "L", 6);
        $this->pdf->row(date('yy-m-d', time()), 0.2, 0, "L", 6);
        $this->pdf->row('500 Dorset RD', 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row($business_name, 0.5, 0, "L", 6);
        $this->pdf->row("Invoice Number ", 0.2, 0, "L", 6);
        $this->pdf->row("Croydon South VIC", 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row("ABN/ACN: ".$abn, 0.5, 0, "L", 6);
        $this->pdf->row($ref_number, 0.2, 0, "L", 6);
        $this->pdf->row("3136", 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row(" ", 0.5, 0, "L", 6);
        $this->pdf->row("ABN:43 613 496 659", 0.2, 0, "L", 6);
        $this->pdf->row("AUSTRALIA", 0.2, 0, "L", 6);

        $this->pdf->ln(20);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Description", 0.5, 0, "L", 10);
        $this->pdf->row("Quantity", 0.1, 0, "R", 6);
        $this->pdf->row("Unit Price", 0.1, 0, "R", 6);
        $this->pdf->row("GST", 0.1, 0, "R", 6);
        $this->pdf->row("AMount AUD", 0.15, 0, "R", 6);
        $this->pdf->ln(10);
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->setFontSize();
        $this->pdf->ln();

        $this->pdf->row("Commission Details", 0.5, 0, "L", 6);
        $this->pdf->row("1", 0.1, 0, "R", 6);
        if ($this->gstType != 2) { //此部分有误 需要更改！

            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling), 0.1, 0, "R", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling / 11), 0.1, 0, "R", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $total_commission_withGST_selling), 0.15, 0, "R", 6);
        }
        if ($this->gstType != 1) { //此部分有误 需要更改！

            $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling), 0.1, 0, "R", 6);
            $this->pdf->row(sprintf("%01.2f", 10).'%', 0.1, 0, "R", 6);
            $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling), 0.15, 0, "R", 6);
            // $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling*1.1),0.15,0,"R",6);
        }

        $this->pdf->ln();
        $this->pdf->row("", 0.7, 0, "R", 6);
        $this->pdf->row("Subtotal", 0.1, 0, "R", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $commission_without_GST_selling), 0.15, 0, "R", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.7, 0, "R", 6);
        $this->pdf->row("Total GST 10%", 0.1, 0, "R", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalCommissionIncGst_gst), 0.15, 0, "R", 6);
        $this->pdf->ln();
        $this->pdf->row("", 0.6, 0, 'R', 6);
        $this->pdf->row("", 0.4, 1, 'C', 0.1);

        $this->pdf->ln();
        $this->pdf->row("", 0.7, 0, "R", 6);
        $this->pdf->row("Total AUD", 0.1, 0, "R", 6);
        $this->pdf->row("$".sprintf("%1\$.2f", $totalCommissionIncGST), 0.15, 0, "R", 6);

        $this->pdf->ln(30);
        $this->pdf->row("Payments will automaticlly withdraw from account balance.", 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("If can not withdraw ,please pay the bill in 7 days of issued ", 0.7, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("Thank you very much for your business!", 0.7, 0, "L", 6);
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
}

/*
 * Class 订单流水PDF
 */

class settlementReportDispatchingSupplier
{
    public $title;//标题

    public $OrderData;

    public $businessName;

    public $tradingName;

    public $accountNumber;

    public $refNumber;

    public $startTime;

    public $endTime;

    public $returnAddress;

    public $gstType;

    public $deliveryFeeType;


    private $fitInPage;

    function __construct()
    {
        $this->pdf = new pdfGenerator();
        $this->fitInPage = false;
    }

    function setStarttime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    function setBSB($bsb)
    {
        $this->bsb = $bsb;

        return $this;
    }

    function setAccountNumber1($accountNumber1)
    {
        $this->accountNumber1 = $accountNumber1;

        return $this;
    }

    function transactionData($value = '')
    {
        $this->transactionData = $value;

        return $this;
    }

    function dataSet1($value = '')
    {
        $this->dataSet1 = $value;

        return $this;
    }

    function dataSet2($value = '')
    {
        $this->dataSet2 = $value;

        return $this;
    }

    function dataSet3($value = '')
    {
        $this->dataSet3 = $value;

        return $this;
    }

    function dataSet4($value = '')
    {
        $this->dataSet4 = $value;

        return $this;
    }

    function setGstType($gstType)
    {
        $this->gstType = $gstType;

        return $this;
    }

    function setDeliveryFeeType($deliveryFeeType)
    {
        $this->deliveryFeeType = $deliveryFeeType;

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
            $headerTag = "From ".$this->startTime." To ".$this->endTime;
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

    function setBusinessName($businessName)
    {
        $this->businessName = $businessName;

        return $this;
    }

    function setABN($abn)
    {
        $this->abn = $abn;

        return $this;
    }

    function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    function setCommissionRate($commissionRate)
    {
        $this->commissionRate = $commissionRate;

        return $this;
    }

    function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    function setRefNumber($refNumber)
    {
        $this->refNumber = $refNumber;

        return $this;
    }

    function generatePDF($thisLang)
    {
        $this->pdf->setLogo('');

        $this->pdf->SetLeftMargin(10);

        $this->pdf->AddPage();
        $business_name = $this->businessName;
        $account_name = $this->tradingName;
        $abn = $this->abn;
        $account_number = $this->accountNumber;
        $ref_number = $this->refNumber;

        $this->pdf->ln();

        $this->pdf->row("Business Name: ".$business_name, 0.7, 0, "L", 6);
        $this->pdf->row("Account Number: ".$account_number, 0.3, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Trading Name: ".$account_name, 0.7, 0, "L", 6);
        $this->pdf->row("RefNumber: ".$ref_number, 0.2, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("ABN/ACN: ".$abn, 0.7, 0, "L", 6);
        $this->pdf->row("From: ".$this->startTime.' to '.$this->endTime, 0.1, 0, "L", 6);
        $this->pdf->ln(10);
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->ln(5);

        $totalIncGST = "Total Incl GST ";
        $exGST = "Ex GST";
        $gst = "GST";

        $totalAmountofTranscations = $this->dataSet1['totalIn'];
        $third_party_surcharges = $this->dataSet2['transactionFee'];
        $delivery_fees = $this->dataSet2['deliveryFee'];
        $delivery_fees_exgst = $delivery_fees / 11 * 10;
        $delivery_fees_gst = floatval($delivery_fees) / 11;

        $total_amount_of_selling = $this->dataSet2['totalGoodsSales'];
		$total_discount_code_amend = $this->dataSet2['promotionTotal'];
        $total_amount_of_selling_with_gst = $this->dataSet2['totalGoodsSalesWithGst'];
        $total_amount_of_selling_without_gst = $this->dataSet2['totalGoodsSalesNoGst'];
        $product_Damage_or_Missing_Without_gst = $this->dataSet2['totalAmendNoGst'];
        $product_Damage_or_Missing_With_gst = $this->dataSet2['totalAmendWithGst'];

        $net_sales_ex_GST = $total_amount_of_selling_without_gst - $product_Damage_or_Missing_Without_gst;
		$net_sales_with_GST = $total_amount_of_selling_with_gst - $product_Damage_or_Missing_With_gst;
		// 如果有折扣金额，且折扣金额小于不包含gst销售总额，则先从这里扣除
		
		if($total_discount_code_amend<=$net_sales_ex_GST) {
			
			 $net_sales_ex_GST = $net_sales_ex_GST-$total_discount_code_amend;
		}else{
			
			 $net_sales_with_GST = $net_sales_with_GST-$total_discount_code_amend;
		}
		
     

        $net_sales_with_GST_sell = $net_sales_with_GST / 11 * 10;
        $net_sales_with_GST_sell_GST = $net_sales_with_GST / 11;
       

        $subTotal_Net_sales = $net_sales_with_GST + $net_sales_ex_GST;

        $subTotal_Net_sales_ex_gst = $net_sales_with_GST_sell + $net_sales_ex_GST;
        $subTotal_Net_sales_gst = $net_sales_with_GST_sell_GST;

        $total_commission_withGST_selling = $net_sales_with_GST * $this->commissionRate; //有gst 部分收取commission
        $commission_without_GST_selling = $net_sales_ex_GST * $this->commissionRate;  //无gst 收取commission
        $commission_without_GST_selling_plusGST = $commission_without_GST_selling * 0.1;///无gst 收取commission

        $commission_rebate = $this->dataSet2['commissionRebate']['rebate']['commission']['amount'];
        $commission_rebate_ex_gst = $this->dataSet2['commissionRebate']['rebate']['commission']['amount_ex_gst'];
        $commission_rebate_gst = $this->dataSet2['commissionRebate']['rebate']['commission']['gst'];

        $promotion_rebate = $this->dataSet2['commissionRebate']['rebate']['promotion']['amount'];
        $promotion_rebate_ex_gst = $this->dataSet2['commissionRebate']['rebate']['promotion']['amount_ex_gst'];
        $promotion_rebate_gst = $this->dataSet2['commissionRebate']['rebate']['promotion']['gst'];
        $this->pdf->ln();

        $this->pdf->row(" ", 0.55, 0, "L", 6);
        $this->pdf->row($totalIncGST, 0.15, 0, "L", 6);
        $this->pdf->row($exGST, 0.15, 0, "L", 6);
        $this->pdf->row($gst, 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln(10);
        $this->pdf->setFontSize(10);
        $this->pdf->row("Amount of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalAmountofTranscations), 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln();
        $this->pdf->row("DiscountCode amend: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($total_discount_code_amend), 0.15, 0, "L", 6);

        $this->pdf->ln();

        $this->pdf->row("Amount of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalAmountofTranscations), 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln(10);
        $this->pdf->setFontSize(10);
        $this->pdf->row("Breakdown of Transcations: ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->ln();

        $this->pdf->row("1)Third party surcharges: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($third_party_surcharges), 0.15, 0, "L", 6);
        $this->pdf->ln();
        // $this->pdf->row("",1,1,'C',0.1);

        $platformChargeDeliveryFee = $this->deliveryFeeType == 0 ? '(Platform Charge)' : '';
        $this->pdf->row("2)Delivery fees$platformChargeDeliveryFee: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($delivery_fees), 0.15, 0, "L", 6);
        if($this->deliveryFeeType == 1) {
            $this->pdf->row($this->displayAmount($delivery_fees_exgst), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($delivery_fees_gst), 0.15, 0, "L", 6);
        }
        

        $this->pdf->ln();
        $this->pdf->row("3)Total amount of selling: ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($total_amount_of_selling), 0.15, 0, "L", 6);

        $this->pdf->ln(10);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Sales by Product Type : ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->ln();

        if ($this->gstType != 2) {
            $this->pdf->row("With GST  sales (Taxable sales): ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($total_amount_of_selling_with_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("  Product Damage or Missing: ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($product_Damage_or_Missing_With_gst), 0.15, 0, "L", 6);

           
		
			$this->pdf->ln();
            $this->pdf->row("SubTotal Net sales (Taxable sales): ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($net_sales_with_GST), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($net_sales_with_GST_sell), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($net_sales_with_GST_sell_GST), 0.15, 0, "L", 6);
            $this->pdf->ln(10);
        }

        if ($this->gstType != 1) {
            $this->pdf->row("GST Free Product: ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($total_amount_of_selling_without_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
			$this->pdf->row("DiscountCode amend: ", 0.55, 0, "L", 6);
			$this->pdf->row($this->displayAmount($total_discount_code_amend), 0.15, 0, "L", 6);
			
			$this->pdf->ln();
            $this->pdf->row("   Product Damage or Missing: ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($product_Damage_or_Missing_Without_gst), 0.15, 0, "L", 6);

            $this->pdf->ln();
            $this->pdf->row("SubTotal Net sales (No Taxable sales): ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($net_sales_ex_GST), 0.15, 0, "L", 6);

            $this->pdf->ln(10);
        }

        $this->pdf->ln();
        $this->pdf->setFontSize(10);
        $this->pdf->row("SubTotal Net sales : ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($subTotal_Net_sales), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($subTotal_Net_sales_ex_gst), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($subTotal_Net_sales_gst), 0.15, 0, "L", 6);
        $this->pdf->setFontSize();

        $this->pdf->ln(10);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Commission Details (See next Page Invoice): ", 0.55, 0, "L", 6);
        $this->pdf->setFontSize();

        $totalCommissionIncGST = $total_commission_withGST_selling + $commission_without_GST_selling * 1.1 + $commission_rebate;
        $totalCommissionIncGST_exgst = $total_commission_withGST_selling / 11 * 10 + $commission_without_GST_selling + $commission_rebate_ex_gst;
        $totalCommissionIncGst_gst = $total_commission_withGST_selling / 11 + $commission_without_GST_selling * 0.1 + $commission_rebate_gst;

        $this->pdf->ln();
        $this->pdf->setFontSize();

        $this->pdf->row("Total Commission charged", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalCommissionIncGST), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalCommissionIncGST_exgst), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalCommissionIncGst_gst), 0.15, 0, "L", 6);

        if($promotion_rebate > 0) {
            $this->pdf->ln(10);
            $this->pdf->setFontSize(10);
            $this->pdf->row("Rebate Details:", 0.55, 0, "L", 6);
            $this->pdf->setFontSize();

            $this->pdf->ln();
            $this->pdf->setFontSize();

            $this->pdf->row("Total Rebate charged", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($promotion_rebate), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($promotion_rebate_ex_gst), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($promotion_rebate_gst), 0.15, 0, "L", 6);
        }

        $this->pdf->ln(20);

        $totalAccountBalanceIncGST = $subTotal_Net_sales  - $totalCommissionIncGST + $promotion_rebate;
        $totalAccountBalance_exgst = $subTotal_Net_sales_ex_gst - $totalCommissionIncGST_exgst + $promotion_rebate_ex_gst;
        $totalAccountBalance_gst = $subTotal_Net_sales_gst  - $totalCommissionIncGst_gst + $promotion_rebate_gst;

        $includeDeliveryFee = '';
        if($this->deliveryFeeType == 1) {
            $includeDeliveryFee .= ' + Delivery fees';
            $totalAccountBalanceIncGST += $delivery_fees;
            $totalAccountBalance_exgst += $delivery_fees_exgst;
            $totalAccountBalance_gst += $delivery_fees_gst;
        }
        $hasPromotionRebate = $promotion_rebate > 0 ? ' + Promotion Rebate' : '';

        $this->pdf->row("Total Account Balance : ", 0.55, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalAccountBalanceIncGST), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalAccountBalance_exgst), 0.15, 0, "L", 6);
        $this->pdf->row($this->displayAmount($totalAccountBalance_gst), 0.15, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Balance = Net sales$includeDeliveryFee -Total commissions(Inc GST) $hasPromotionRebate", 0.55, 0, "L", 6);

        $this->pdf->ln(29);

        $this->pdf->row("", 0.6, 1, 'C', 0.1);
        // $this->pdf->row("",0.4,1,'C',0.1);
        $this->pdf->ln();

        //commission rebate 发票
        if(count($this->dataSet2['commissionRebate']['products']) > 0) {
            $this->pdf->AddPage();
            $this->pdf->ln(5);
            $this->pdf->row(" ", 0.40, 0, "L", 6);
            $this->pdf->row('Qty', 0.15, 0, "L", 6);
            $this->pdf->row($totalIncGST, 0.15, 0, "L", 6);
            $this->pdf->row($exGST, 0.15, 0, "L", 6);
            $this->pdf->row($gst, 0.15, 0, "L", 6);
            $this->pdf->ln(5);
            $this->pdf->row("", 1, 1, 'C', 0.1);

            foreach ($this->dataSet2['commissionRebate']['products'] as $product) {
                $this->pdf->ln(5);
                $this->pdf->row("Product Name:", 0.2, 0, "L", 6);
                $this->pdf->row($product['menu_cn_name'], 0.15, 0, "L", 6);
                $this->pdf->ln(5);
                $this->pdf->row("Restaurant Menu Id:", 0.2, 0, "L", 6);
                $this->pdf->row($product['restaurant_menu_id'], 0.15, 0, "L", 6);
                $this->pdf->ln(5);
                $this->pdf->row("Include Gst:", 0.2, 0, "L", 6);
                $this->pdf->row($product['include_gst'] == 1 ? 'Yes' : 'No', 0.15, 0, "L", 6);
                $this->pdf->ln(10);
                $this->pdf->row("Original Sales: ", 0.40, 0, "L", 6);
                $this->pdf->row($product['quantity'], 0.15, 0, "L", 6);
                $this->pdf->row($this->displayAmount($product['original_total_sale']['amount'], $product['original_single_sale']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 0) {
                    $this->pdf->row($this->displayAmount($product['original_total_sale']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount($product['original_total_sale']['gst']), 0.15, 0, "L", 6);
                }

                $this->pdf->ln(5);
                $this->pdf->row("Original Commission: ", 0.55, 0, "L", 6);
                $this->pdf->row($this->displayAmount(-1 * $product['original_commission']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 1) {
                    $this->pdf->row($this->displayAmount(-1 * $product['original_commission']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount(-1 * $product['original_commission']['gst']), 0.15, 0, "L", 6);
                }

                $this->pdf->ln(10);
                $this->pdf->row("Promotion Sales: ", 0.40, 0, "L", 6);
                $this->pdf->row($product['quantity'], 0.15, 0, "L", 6);
                $this->pdf->row($this->displayAmount($product['promotion_total_sale']['amount'], $product['promotion_single_sale']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 0) {
                    $this->pdf->row($this->displayAmount($product['promotion_total_sale']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount($product['promotion_total_sale']['gst']), 0.15, 0, "L", 6);
                }

                $this->pdf->ln(5);
                $this->pdf->row("Promotion Commission: ", 0.55, 0, "L", 6);
                $this->pdf->row($this->displayAmount(-1 * $product['promotion_commission']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 1) {
                    $this->pdf->row($this->displayAmount(-1 * $product['promotion_commission']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount(-1 * $product['promotion_commission']['gst']), 0.15, 0, "L", 6);
                }

                $this->pdf->ln(10);
                $this->pdf->row("Commission Rebate: ", 0.55, 0, "L", 6);
                $this->pdf->row($this->displayAmount(-1 * $product['commission_rebate']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 1) {
                    $this->pdf->row($this->displayAmount(-1 * $product['commission_rebate']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount(-1 * $product['commission_rebate']['gst']), 0.15, 0, "L", 6);
                }

                $this->pdf->ln(5);
                $this->pdf->row("Promotion Rebate: ", 0.55, 0, "L", 6);
                $this->pdf->row($this->displayAmount($product['promotion_rebate']['amount']), 0.15, 0, "L", 6);
                if ($product['include_gst'] != 0) {
                    $this->pdf->row($this->displayAmount($product['promotion_rebate']['amount_ex_gst']), 0.15, 0, "L", 6);
                    $this->pdf->row($this->displayAmount($product['promotion_rebate']['gst']), 0.15, 0, "L", 6);
                }
                $this->pdf->ln(10);
                $this->pdf->row("", 1, 1, 'C', 0.1);
            }

            $this->pdf->ln(10);
            $this->pdf->row("Total Commission Rebate: ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['commission']['amount']), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['commission']['amount_ex_gst']), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['commission']['gst']), 0.15, 0, "L", 6);

            $this->pdf->ln(5);
            $this->pdf->row("Total Promotion Rebate: ", 0.55, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['promotion']['amount']), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['promotion']['amount_ex_gst']), 0.15, 0, "L", 6);
            $this->pdf->row($this->displayAmount($this->dataSet2['commissionRebate']['rebate']['promotion']['gst']), 0.15, 0, "L", 6);
            $this->pdf->ln(10);
        }

        $this->pdf->AddPage();
        $this->pdf->row("Payments will automaticlly Transfer to business Account in 2 days  .", 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("Account Name:".$this->accountName, 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("BSB: ".$this->bsb." Account Number: ".$this->accountNumber1, 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("If can not received  ,please contact Ubonus Payment department ", 0.7, 0, "L", 6);
        $this->pdf->ln();
        $this->pdf->row("Thank you very much for your business!", 0.7, 0, "L", 6);
        $this->pdf->ln();

        //另外一页 出commission invoice 发票

        $this->pdf->setLogo(DOC_DIR.'themes/zh-cn/images/logo-print.png');
        $this->pdf->setTitle("");
        $this->pdf->AddPage();

        $this->pdf->ln();

        $this->pdf->setFontSize(20);
        $this->pdf->row("TAX INVOICE ", 0.6, 0, "L", 6);
        $this->pdf->setFontSize();
        $this->pdf->row("Invoice Date: ", 0.2, 0, "L", 6);
        $this->pdf->row("CityB2B  Pty Ltd", 0.2, 0, "L", 6);

        $this->pdf->ln();

        $this->pdf->row("", 0.6, 0, "L", 6);
        $this->pdf->row(date('yy-m-d', time()), 0.2, 0, "L", 6);
        $this->pdf->row('500 Dorset RD', 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row($business_name, 0.5, 0, "L", 6);
        $this->pdf->row("Invoice Number ", 0.2, 0, "L", 6);
        $this->pdf->row("Croydon South VIC", 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row("ABN/ACN: ".$abn, 0.5, 0, "L", 6);
        $this->pdf->row($ref_number, 0.2, 0, "L", 6);
        $this->pdf->row("3136", 0.2, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("", 0.1, 0, "L", 6);
        $this->pdf->row(" ", 0.5, 0, "L", 6);
        $this->pdf->row("ABN:43 613 496 659", 0.2, 0, "L", 6);
        $this->pdf->row("AUSTRALIA", 0.2, 0, "L", 6);

        $this->pdf->ln(20);

        $this->pdf->setFontSize(10);
        $this->pdf->row("Description", 0.6, 0, "L", 10);
        $this->pdf->row("Unit Price", 0.1, 0, "R", 6);
        $this->pdf->row("GST", 0.1, 0, "R", 6);
        $this->pdf->row("AMount AUD", 0.15, 0, "R", 6);
        $this->pdf->ln(10);
        $this->pdf->row("", 1, 1, 'C', 0.1);
        $this->pdf->setFontSize();
        $this->pdf->ln();

        if ($this->gstType != 2) {
            $this->pdf->row("Taxable Commission Details", 0.6, 0, "L", 6);
            $this->pdf->row($this->displayAmount($total_commission_withGST_selling / 11 * 10), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($total_commission_withGST_selling / 11), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($total_commission_withGST_selling), 0.15, 0, "R", 6);
            $this->pdf->ln();
        }

        if ($this->gstType != 1) {
            $this->pdf->row("No Taxable Commission Details(10%)", 0.6, 0, "L", 6);
            $this->pdf->row($this->displayAmount($commission_without_GST_selling), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($commission_without_GST_selling * 0.1), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($commission_without_GST_selling + $commission_without_GST_selling * 0.1), 0.15, 0, "R", 6);

            $this->pdf->ln();
        }

        if($this->dataSet2['commissionRebate']['rebate']['commission']['amount'] > 0) {
            $this->pdf->row("Commission Rebate", 0.6, 0, "L", 6);
            $this->pdf->row($this->displayAmount($commission_rebate_ex_gst), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($commission_rebate_gst), 0.1, 0, "R", 6);
            $this->pdf->row($this->displayAmount($commission_rebate), 0.15, 0, "R", 6);

            $this->pdf->ln();
        }

        $this->pdf->ln();
        $this->pdf->row("", 0.6, 0, 'R', 6);
        $this->pdf->row("", 0.4, 1, 'C', 0.1);

        $this->pdf->ln();
        $this->pdf->row("", 0.7, 0, "R", 6);
        $this->pdf->row("Total AUD", 0.1, 0, "R", 6);
        $this->pdf->row($this->displayAmount($totalCommissionIncGST), 0.15, 0, "R", 6);

        $this->pdf->ln(30);

        $this->pdf->ln();
        $this->pdf->row("", 0.6, 1, 'C', 0.1);
        // $this->pdf->row("",0.4,1,'C',0.1);
        $this->pdf->ln();
        $this->pdf->row("Payments will automaticlly withdraw from account balance.", 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("Account Name:".$this->accountName, 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("BSB: ".$this->bsb." Account Number: ".$this->accountNumber1, 0.7, 0, "L", 6);
        $this->pdf->ln();

        $this->pdf->row("If can not withdraw ,please pay the bill in 7 days of issued ", 0.7, 0, "L", 6);

        $this->pdf->ln();
        $this->pdf->row("Thank you very much for your business!", 0.7, 0, "L", 6);
        $this->pdf->ln();
    }

    function displayAmount($amount, $single = 0)
    {
        $amountSymbol = $amount >=0 ? ' $' : '-$';
        $result = $amountSymbol.sprintf("%1\$.2f", abs($amount));
        if($single != 0 && $single != $amount) {
            $singleSymbol = $single >=0 ? ' $' : '-$';
            $singleToString = $singleSymbol.sprintf("%1\$.2f", abs($single));
            $result = "$result($singleToString)";
        }
        return $result;
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

?>

