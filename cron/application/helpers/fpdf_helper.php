<?php if (!defined('BASEPATH')) { exit('No direct script access allowed');}

require_once APPPATH.'/libraries/fpdf/pdf.php';

/**
 * Used for generate pdf
 * @param array $data
 * @return 
 */
function generate_pdf($contest_pdf_name,$data){
	$sports_id = isset($data['contest_info']['sports_id']) ? $data['contest_info']['sports_id'] : "";
	$pdf = new PDF();
	$pdf->AddPage('L');
	$pdf->CustomHeader($data['contest_info']);
	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak('auto',7);
	$cheader = "Player 1";
	if(isset($data['c_vc']['c_point']) && $data['c_vc']['c_point'] > 0){
		$cheader = "Player 1 (C)";
		if($sports_id == MOTORSPORT_SPORTS_ID){
			$cheader = "Player 1 (T)";
		}
	}
	$vcheader = "Player 2";
	if(isset($data['c_vc']['vc_point']) && $data['c_vc']['vc_point'] > 0){
		$vcheader = "Player 2 (VC)";
	}
	$total_column = 11;//total player column
	if(isset($data['team_list']['0']) && count($data['team_list']['0']) > 0){
        $pl_cnt = count($data['team_list']['0']);
        $total_column = $pl_cnt - 2;
    }
	// Column headings
	$header = array('User Name', 'Team Name', $cheader, $vcheader);
	for($i=3;$i<=$total_column;$i++){
		$header[] = 'Player '.$i;
	}
	// Data loading
	$pdf->BasicTable($header,$data['team_list']);
	$pdf->Output('F', $contest_pdf_name, true);
	return true;
}