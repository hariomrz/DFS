<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

require_once APPPATH.'../../adminapi/application/libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options as Options;

/**
 * Used for generate pdf
 * @param array $data
 * @return 
 */
function generate_pdf($pdf_name,$html,$type="portrait"){

    $options = new Options();
    $options->setIsPhpEnabled(true);
    $options->setIsRemoteEnabled(true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    //$dompdf->setPaper("A4", 'landscape');
    $dompdf->set_paper("A4", $type);
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream($pdf_name, array("Attachment" => 1));
    return true;
}
