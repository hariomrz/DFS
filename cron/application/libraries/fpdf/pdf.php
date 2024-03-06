<?php
require('fpdf.php');

class PDF extends FPDF
{
    // Load data
    function LoadData($file)
    {
        // Read file lines
        $lines = file($file);
        $data = array();
        foreach($lines as $line)
            $data[] = explode(';',trim($line));
        return $data;
    }

    function get_prize_pool($contest){
        $prize_detail = json_decode($contest['prize_distibution_detail'],TRUE);
        $prize_pool = $contest['prize_pool'];
        $tmp_prize_pool = 0;
        if(!empty($prize_detail)){
            foreach($prize_detail as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                    $tmp_prize_pool = $tmp_prize_pool + $prize['max_value'];
                }else if(!isset($prize['prize_type'])){
                    $tmp_prize_pool = $tmp_prize_pool + $prize['amount'];
                }
            }
            $prize_pool = $tmp_prize_pool;
        }
        return $prize_pool;
    }

    function CustomHeader($data)
    {  
        if($data['int_version']==1){
           $fixture = 'Game';
        }else{
            $fixture =  'Fixture';
        }
        if ($this->page == 1)
        {
            $prize_pool = $this->get_prize_pool($data);
            $prize_pool = number_format($prize_pool,"2",".","");
            // Logo
            $this->SetFillColor(21,32,58);
            $this->SetTextColor(255);
            $this->Image($data['logo'],10,6,30);
            // Arial bold 15
            $this->SetFont('Arial','',10);
            // Move to the right
            $this->Cell(50);
            // Title
            $this->Cell(55,10, $fixture.' : '.$data['collection_name'],1,0,'C',true);
            $this->Cell(70,10,'Schedule Date : '.$data['season_scheduled_date']."(UTC)",1,0,'C',true);
            $this->Cell(35,10,'Prize Pool : '.$prize_pool,1,0,'C',true);
            $this->Cell(35,10,'Entry Fee : '.$data['entry_fee'],1,0,'C',true);
            $this->Cell(35,10,'Members : '.$data['total_user_joined'],1,0,'C',true);
            // Line break
            $this->Ln(15);
        }
    }

    // Simple table
    function BasicTable($header, $data)
    {
        // Header
        $this->SetFillColor(21,32,58);
        $this->SetTextColor(255);
        //$cell_size = array("18","20","22","22","22","22","22","22","22","22","22","22","22");
        $plc_size = "22";
        $pl_cnt = count($header) - 2;
        if($pl_cnt < 11){
            $plc_size = floor(242 / $pl_cnt);
        }
        $cell_size = array("18","20");
        for($i=0;$i<$plc_size;$i++){
            $cell_size[] = $plc_size;
        }
        foreach($header as $key=>$col){
            $csize = isset($cell_size[$key])?$cell_size[$key]:22;
            $this->Cell($csize,10,$col,1,0,'C',true);
        }
        $this->Ln();
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        // Data
        foreach($data as $row)
        {
            foreach($row as $key=>$col){
                $csize = isset($cell_size[$key])?$cell_size[$key]:22;
                $this->Cell($csize,7,$col,1,0,'L',true);
            }
            $this->Ln();
        }
    }
}
?>