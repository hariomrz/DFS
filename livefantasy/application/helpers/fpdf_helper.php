<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

require_once APPPATH.'/libraries/fpdf/pdf.php';

/**
 * Used for generate pdf
 * @param array $data
 * @return 
 */
function generate_pdf($contest_pdf_name,$data){
	
	$pdf = new PDF();
	$pdf->AddPage('L');
	$pdf->CustomHeader($data['contest_info']);
	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak('auto',7);
	$cheader = "Item 1";
	if(CAPTAIN_POINT > 0){
		$cheader = "Item 1 (C)";
	}

	$scheader = "Item 2";
	if(VICE_CAPTAIN_POINT > 0){
		$scheader = "Item 2 (S)";
	}
	
	$total_column = 10;
	if(isset($data['tc']))
	{
		$total_column = $data['tc'];//total player column
	}
	if(isset($data['team_list']['0']) && count($data['team_list']['0']) > 0){
        $pl_cnt = count($data['team_list']['0']);
        $total_column = $pl_cnt - 1;
    }
	// Column headings
	$header = array('User Name', 'Team Name', $cheader,$scheader);
	for($i=3;$i<$total_column;$i++){
		$header[] = 'Item '.$i;
	}

	// Data loading
	$pdf->BasicTable($header,$data['team_list']);
	$pdf->Output('F', $contest_pdf_name, true);
	return true;
}