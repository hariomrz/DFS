<?php echo $this->load->view("emailer/header",array(),TRUE); ?>
<!--Start middle section-->
<tr>
    <td colspan="3" class="info-td">
        <h4>
            Hello Team,
        </h4>
        <p>
            We regret to inform you that there is some technical issue in Team generate for below match. Please check it ASAP.
        </p>
        <p>
            Match Name : <b><?php echo $data['match']; ?></b><br/>
            Match ID : <b><?php echo $data['season_game_uid']; ?></b><br/>
            Match Date : <b><?php echo $data['season_scheduled_date']; ?></b><br/>
            Collection ID : <b><?php echo $data['collection_id']; ?></b><br/>
        </p>
    </td>
</tr>
<!--End middle section-->
<?php echo $this->load->view("emailer/footer",array(),TRUE); ?>