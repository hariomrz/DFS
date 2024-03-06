<?php 
$this->load->view("include/header");
if (isset($content_view))
{
    $this->load->view("$content_view");
} 
$this->load->view("include/footer");
?>