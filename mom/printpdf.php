<?php
// 2020-07-13 - js said mim incoming don't charge
//Based on HTML2PDF by Clément Lavoillotte
//From http://www.fpdf.org/en/script/script50.php
require('lib/fpdf.php');
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

define("NOBORDER",0);
define("FRAME",1);
//function hex2dec
//returns an associative array (keys: R,G,B) from a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['G']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

//conversion pixel -> millimeter in 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
////////////////////////////////////

class PDF extends FPDF
{
    //variables of html parser
    protected $B;
    protected $I;
    protected $U;
    protected $HREF;
    protected $fontList;
    protected $issetfont;
    protected $issetcolor;

    function __construct($orientation='P', $unit='mm', $format='A4')
    {
        //Call parent constructor
        parent::__construct($orientation,$unit,$format);

        //Initialization
        $this->B=0;
        $this->I=0;
        $this->U=0;
        $this->HREF='';

        $this->tableborder=0;
        $this->tdbegin=false;
        $this->tdwidth=0;
        $this->tdheight=0;
        $this->tdalign="L";
        $this->tdbgcolor=false;

        $this->oldx=0;
        $this->oldy=0;

        $this->fontlist=array("arial","times","courier","helvetica","symbol");
        $this->issetfont=false;
        $this->issetcolor=false;
    }

    //////////////////////////////////////
    //html parser

    function WriteHTML($html)
    {
        $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><hr><td><tr><table><sup>"); //remove all unsupported tags
        $html=str_replace("\n",'',$html); //replace carriage returns with spaces
        $html=str_replace("\t",'',$html); //replace carriage returns with spaces
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //explode the string
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                elseif($this->tdbegin) {
                    if(trim($e)!='' && $e!="&nbsp;") {
                        $this->Cell($this->tdwidth,$this->tdheight,$e,$this->tableborder,'',$this->tdalign,$this->tdbgcolor);
                    }
                    elseif($e=="&nbsp;") {
                        $this->Cell($this->tdwidth,$this->tdheight,'',$this->tableborder,'',$this->tdalign,$this->tdbgcolor);
                    }
                }
                else
                    $this->Write(5,stripslashes(txtentities($e)));
            }
            else
            {
                //Tag
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract attributes
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        //Opening tag
        switch($tag){

            case 'SUP':
                if( !empty($attr['SUP']) ) {    
                    //Set current font to 6pt     
                    $this->SetFont('','',6);
                    //Start 125cm plus width of cell to the right of left margin         
                    //Superscript "1" 
                    $this->Cell(2,2,$attr['SUP'],0,0,'L');
                }
                break;

            case 'TABLE': // TABLE-BEGIN
                if( !empty($attr['BORDER']) ) $this->tableborder=$attr['BORDER'];
                else $this->tableborder=0;
                break;
            case 'TR': //TR-BEGIN
                break;
            case 'TD': // TD-BEGIN
                if( !empty($attr['WIDTH']) ) $this->tdwidth=($attr['WIDTH']/4);
                else $this->tdwidth=40; // Set to your own width if you need bigger fixed cells
                if( !empty($attr['HEIGHT']) ) $this->tdheight=($attr['HEIGHT']/6);
                else $this->tdheight=6; // Set to your own height if you need bigger fixed cells
                if( !empty($attr['ALIGN']) ) {
                    $align=$attr['ALIGN'];        
                    if($align=='LEFT') $this->tdalign='L';
                    if($align=='CENTER') $this->tdalign='C';
                    if($align=='RIGHT') $this->tdalign='R';
                }
                else $this->tdalign='L'; // Set to your own
                if( !empty($attr['BGCOLOR']) ) {
                    $coul=hex2dec($attr['BGCOLOR']);
                        $this->SetFillColor($coul['R'],$coul['G'],$coul['B']);
                        $this->tdbgcolor=true;
                    }
                $this->tdbegin=true;
                break;

            case 'HR':
                if( !empty($attr['WIDTH']) )
                    $Width = $attr['WIDTH'];
                else
                    $Width = $this->w - $this->lMargin-$this->rMargin;
                $x = $this->GetX();
                $y = $this->GetY();
                $this->SetLineWidth(0.2);
                $this->Line($x,$y,$x+$Width,$y);
                $this->SetLineWidth(0.2);
                $this->Ln(1);
                break;
            case 'STRONG':
                $this->SetStyle('B',true);
                break;
            case 'EM':
                $this->SetStyle('I',true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag,true);
                break;
            case 'A':
                $this->HREF=$attr['HREF'];
                break;
            case 'IMG':
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if(!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                    $coul=hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
                    $this->issetcolor=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist) && isset($attr['SIZE']) && $attr['SIZE']!='') {
                    $this->SetFont(strtolower($attr['FACE']),'',$attr['SIZE']);
                    $this->issetfont=true;
                }
                break;
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='SUP') {
        }

        if($tag=='TD') { // TD-END
            $this->tdbegin=false;
            $this->tdwidth=0;
            $this->tdheight=0;
            $this->tdalign="L";
            $this->tdbgcolor=false;
        }
        if($tag=='TR') { // TR-END
            $this->Ln();
        }
        if($tag=='TABLE') { // TABLE-END
            $this->tableborder=0;
        }

        if($tag=='STRONG')
            $tag='B';
        if($tag=='EM')
            $tag='I';
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='FONT'){
            if ($this->issetcolor==true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont=false;
            }
        }
    }

    function SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s) {
            if($this->$s>0)
                $style.=$s;
        }
        $this->SetFont('',$style);
    }

    function PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }

}//end of class

class INVOICE {
    protected $sms_charge = 0.044;
    protected $mim_charge = 0.084;
    protected $mau_charge = 0.009;
    protected $mau_free = 12000;
    protected $gst = 0.08;
    protected $dept = "Department";
    protected $pic = "Person In Charge";
    protected $invno = "TX";
    protected $fordate = "MMM-YYYY";
    protected $filename = "file.pdf";

    protected $sms_gen_o = 0;
    protected $sms_gen_i = 0;
    protected $wsa_gen   = 0;
    protected $mau_gen   = 0;
    protected $sms_api_o = 0;
    protected $sms_api_i = 0;
    protected $wsa_api   = 0;
    protected $mau_api   = 0;

    function __construct() {
        $this->sms_gen_o = 0;
        $this->sms_gen_i = 0;
        $this->wsa_gen   = 0;
        $this->mau_gen   = 0;
        $this->sms_api_o = 0;
        $this->sms_api_i = 0;
        $this->wsa_api   = 0;
        $this->mau_api   = 0;
    }
    function detail($v1,$v2,$v3,$v4) {
        $this->pic = $v1;
        $this->dept = $v2;
        $this->invno = $v3;
        $this->fordate = $v4;
    }
    function information_gen($v1,$v2,$v3,$v4) {
        $this->sms_gen_o = $v1;
        $this->sms_gen_i = $v2;
        $this->wsa_gen   = $v3;
        $this->mau_gen   = $v4;
    }
    function information_api($v1,$v2,$v3,$v4) {
        $this->sms_api_o = $v1;
        $this->sms_api_i = $v2;
        $this->wsa_api   = $v3;
        $this->mau_api   = $v4;
    }
    function information_charge($v1,$v2,$v3,$v4) {
        $this->sms_charge = $v1;
        $this->mim_charge = $v2;
        $this->mau_charge = $v3;
        $this->mau_free = $v4;
    }
    function outputas($filename) {
        $this->filename = $filename;
    }
    function print() {
        // -- PARAMETER AREA
        $date = date("d-M-Y");
        $personname = $this->pic;
        $dept = $this->dept;
        $invno = $this->invno;
        $fordate = $this->fordate;

        $t_sms_gen_o = ceil($this->sms_gen_o * $this->sms_charge * 100) / 100;
        $t_sms_gen_i = ceil($this->sms_gen_i * $this->sms_charge * 100) / 100;
        $t_wsa_gen   = ceil($this->wsa_gen * $this->mim_charge * 100) / 100;
        // $t_mau_gen   = ceil($mau_gen * 0.009 * 100) / 100;

        $t_sms_api_o = ceil($this->sms_api_o * $this->sms_charge * 100) / 100;
        $t_sms_api_i = ceil($this->sms_api_i * $this->sms_charge * 100) / 100;
        $t_wsa_api   = ceil($this->wsa_api * $this->mim_charge * 100) / 100;
        // $t_mau_api   = ceil($mau_api * 0.009 * 100) / 100;

        $t_mau_all = ($this->mau_api + $this->mau_gen) <= $this->mau_free ? 0 : ceil(($this->mau_api + $this->mau_gen - $this->mau_free) * $this->mau_charge  * 100) / 100; 
        // $total = $t_sms_gen_o + $t_sms_gen_i + $t_mau_gen + $t_sms_api_o + $t_sms_api_i + $t_wsa_api +  ($t_mau_api + $t_wsa_gen) ;
        $total = $t_sms_gen_o + $t_sms_gen_i + $t_mau_all + $t_sms_api_o + $t_sms_api_i + $t_wsa_api +  $t_wsa_gen;
        $gst = round($total * $this->gst,2);

        // -- END of PARAMETER
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','',28);
        $pdf->Image("/home/msg/www/htdocs/mom/images/TalariaX-head.png");
        $pdf->Cell(0, 0, " ", 0, 0, 'C');
        $pdf->Ln();
        $pdf->Cell(0, 10, "TAX INVOICE", 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Times','',12);
        $pdf->Cell(0, 5, "(GST Reg. No.: 19-8301086-M)", 0, 0, 'C');
        $pdf->Ln();
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $persondetail = <<< END
$personname
$dept
Minitry of Manpower
18 Havelock Rd

Singapore 059764
END;
        $pdf->MultiCell(100,6,$persondetail,NOBORDER,'L');
        $text = <<< END
Invoice No.:
Date:
Ref/P.O.:
END;
        $pdf->SetXY($x + 100, $y);
        $pdf->MultiCell(50,6,$text,NOBORDER,'R');
        $pdf->SetXY($x + 150, $y);
        $text = <<< END
$invno
$date\n

END;
        $pdf->MultiCell(0,6,$text,NOBORDER,'L');
        $pdf->Ln(24);

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->SetFillColor(165,165,165);
        $pdf->SetFont("Times","B");
        $pdf->MultiCell(15,6,"\nS/N",FRAME,'C',1);
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"\nDescription",FRAME,'C',1);
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,"\nQty",FRAME,'C',1);
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"Unit Price\n(SGD)",FRAME,'C',1);
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,"Total Price\n(SGD)",FRAME,'C',1);

        $pdf->SetFont("Times","");
        $pdf->SetFillColor(255,255,255);
        $y = $pdf->GetY();
        $x = $pdf->GetX();

        $pdf->MultiCell(15,9," 1.\n","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,9,"SendQuickASP SMS for $fordate ($dept)\n","LR",'L');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,9,"\n","LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,9,"\n","LR",'C');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,9,"\n","LR",'C');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"----Web Portal----","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6," ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"SMS Sent","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->sms_gen_o,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->sms_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_sms_gen_o,2),"LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"SMS Received","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->sms_gen_i,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->sms_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_sms_gen_i,2),"LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"Whatsapp Template Message","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->wsa_gen,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->mim_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_wsa_gen,2),"LR",'R');
        /*
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"\t ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"MAU","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$mau_gen,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"0.09","LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_mau_gen,2),"LR",'R');*/

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"----API----","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,"\n","LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"SMS Sent","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->sms_api_o,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->sms_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_sms_api_o,2),"LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"SMS Received","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->sms_api_i,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->sms_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_sms_api_i,2),"LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"Whatsapp Template Message","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->wsa_api,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->mim_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_wsa_api,2),"LR",'R');
        /*
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"\t ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"MAU","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$mau_api,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"0.09","LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_mau_api,2),"LR",'R');*/
		$y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"-------------","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6," ","LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6," ","LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6," ","LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LR",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"MAU (Web Portal & API, First ".$this->mau_free." unique are free)","LR",'R');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,$this->mau_gen+$this->mau_api,"LR",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,$this->mau_charge,"LR",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($t_mau_all,2),"LR",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6," ","LRB",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(100,6,"Singapore Dollars and Cents Only.","LRB",'L');
        $pdf->SetXY($x + 115, $y);
        $pdf->MultiCell( 25,6,"\n","LRB",'C');
        $pdf->SetXY($x + 140, $y);
        $pdf->MultiCell( 25,6,"\n","LRB",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,"\n","LRB",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6," ","LRB",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(150,6,"Total Price (SGD):","LRB",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($total,2),"LRB",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,6,"  ","LRB",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(150,6,"Good & Services Tax (GST ".($this->gst*100)."%):","LRB",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,6,number_format($gst,2),"LRB",'R');

        $y = $pdf->GetY();
        $x = $pdf->GetX();
        $pdf->MultiCell(15,12,"  ","LRB",'L');
        $pdf->SetXY($x + 15, $y);
        $pdf->MultiCell(150,12,"Total Payable Amount (SGD):","LRB",'R');
        $pdf->SetXY($x + 165, $y);
        $pdf->MultiCell( 25,12,number_format($total+$gst,2),"LRB",'R');

        $pdf->Ln(1);
        $pdf->SetFillColor(30,30,30);
        $pdf->SetFont('Times',"I");
        $y = $pdf->GetY();
        $pdf->SetXY($x + 15, $y);
        $pdf->SetTextColor(255,255,255);
        $pdf->MultiCell(160,12,"We Appreiciate Your Continuous Support. Thank You.",NOBORDER,'C',1);
        $pdf->SetTextColor(0,0,0);
        $toc = <<< END
<B><U>Terms:</U></B><br>
All prices are in Singapore Dollars, unless otherwise stated. F.O.B. Singapore.<br>
Payment term: 30 Days<br>
All payment(s) should be made by cross cheuqe in favour of "<B>TalariaX Pte Ltd</B>" or via Telegraphic Transfer to Bank Account: <B>651-322463-001, Overseas Chinese Bank Corporation (OCBC), Singapore</B>. Swift: <b>OCBCSGSG</b>
END;
        $pdf->SetFont('Times',"",10);
        // $pdf->MultiCell(0,4,$toc,"",'J');
        $pdf->WriteHTML($toc);

        $pdf->Ln();
        $pdf->SetFont('Times',"I",10);

        $pdf->MultiCell(0,8,"This is computer generated invoices. No signature is required.","",'L');

        /*$pdf->MultiCell(0,8,"Authorised Signature","",'L');
        $pdf->Ln(12);
        $pdf->SetFont('Times',"",10);
        $pdf->MultiCell(0,6,"______________________","",'L');
        $pdf->MultiCell(0,6,"TalariaX Pte Ltd","",'L');*/


        // $pdf->SetAutoPageBreak(false);
        // $pdf->Ln(12);
        $pdf->SetY(-30);
        $pdf->SetFont('Arial',"",8);
        $pdf->SetTextColor(128,128,128);
        $pdf->MultiCell(0,3,"TALARIAX PTE LTD NO. 76 PLAYFAIR ROAD #08-01 LHK SINGAPORE 367996","",'C');
        $pdf->MultiCell(0,3,"TEL: +65 62902991 FAX: +65 62806882 WEB: WWW.TALARIAX.COM","",'C');

        // $pdf->Output();
        $pdf->Output("F","/home/msg/www/htdocs/mom/invoices/".$this->filename);
    }
}

?>
