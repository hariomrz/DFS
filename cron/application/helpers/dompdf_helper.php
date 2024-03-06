<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

require_once APPPATH.'/libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

/**
 * Used for generate pdf
 * @param array $data
 * @return 
 */
function generate_pdf($contest_pdf_name,$html){
	$dompdf = new Dompdf();
    $dompdf->set_paper("A4", 'landscape');
    $dompdf->loadHtml($html);
    $dompdf->render();
    $output = $dompdf->output();
    $file = fopen($contest_pdf_name,"w");
    fputs($file,$output);
    fclose($file);
}