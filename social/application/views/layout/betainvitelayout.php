<?php 
$this->load->view("include/betainviteheader");
if (isset($content_view))
{
    $this->load->view("$content_view");
} 

?>

<div id="success_message" class="notifications success" style="display: block;">
    <div class="content">
        <span>SUCCESS!</span><span id="spn_noti">  Deleted successfully.</span>
        <div class="icon"></div>
    </div>
</div>

<div id="error_message" class="notifications fail" style="display: block;">
    <div class="content">
        <span>FAILURE!</span><span id="spn_noti">  Deleted successfully.</span>
        <div class="icon"></div>
    </div>
</div>
<?php $this->load->view("include/footer"); ?>