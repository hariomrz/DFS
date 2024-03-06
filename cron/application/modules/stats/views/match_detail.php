<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="1" />
</head>
<body>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css'>
<style type="text/css">
    .table{color:#333;}
    .rdft{color:#ff0000;}
    .pg_h3{margin-left:20px;}
    .pg_cnt{margin:20px; border:#ccc solid 1px; overflow: auto;}
    .chdiv{width: 100%; margin: 5px;}
    table td,table th{text-align: center;vertical-align: middle!important;}
    table td:nth-child(1),table th:nth-child(1),table td:nth-child(2),table th:nth-child(2){text-align: left;}
    .chdiv table{margin-bottom: 0px;}
    .chdiv table td{border: #ccc solid 1px; text-align: center;}
    .chdiv table tr.pertr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic;}
    .sttd{width: 10%;}
    .sttd:nth-child(even){background: #f1f1f1;}
    .sttd .chdiv{margin: 5px 0px;}
    .sttd label{font-size: 12px;}
    .sttd .chdiv{width: 175px;}
    .chdiv table tr.mwtr td{border: none; padding: 2px 8px; font-size: 12px; font-style: italic; background-color: #ccc;}
</style>
<h3 class="pg_h3"><?php echo $data['collection_name']." - ".$data['season_scheduled_date']; ?>(UTC)</h3>
<div class="pg_cnt">
    <table class='table table-striped'>
        <thead>
            <tr>
                <th>FieldName</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>CollectionID</td>
                <td><?php echo $data['collection_master_id']; ?></td>
            </tr>
            <tr>
                <td>CollectionName</td>
                <td><?php echo $data['collection_name']; ?></td>
            </tr>
            <tr>
                <td>ScheduledDate(UTC)</td>
                <td><?php echo $data['season_scheduled_date']; ?></td>
            </tr>
            <tr>
                <td>ScheduledDate(IST)</td>
                <td><?php echo convert_to_client_timezone($data['season_scheduled_date'],"d M Y, h:i A"); ?></td>
            </tr>
            <tr>
                <td>Status</td>
                <td><?php echo $data['status']; ?></td>
            </tr>
            <tr>
                <td>LineupProcess</td>
                <td><?php echo $data['is_lineup_processed']; ?></td>
            </tr>
            <tr>
                <td>MatchInfo</td>
                <td>
                    <table class='table table-striped' border="0">
                        <tbody>
                            <tr>
                                <td>season_game_uid</td>
                                <td><?php echo $data['match']['season_game_uid']; ?></td>
                            </tr>
                            <tr>
                                <td>scheduled_date</td>
                                <td><?php echo $data['match']['season_scheduled_date']; ?></td>
                            </tr>
                            <tr>
                                <td>home_uid</td>
                                <td><?php echo $data['match']['home_uid']; ?></td>
                            </tr>
                            <tr>
                                <td>away_uid</td>
                                <td><?php echo $data['match']['away_uid']; ?></td>
                            </tr>
                            <tr>
                                <td>playing_announce</td>
                                <td><?php echo $data['match']['playing_announce']; ?></td>
                            </tr>
                            <tr>
                                <td>playing_list</td>
                                <td><?php echo $data['match']['playing_list']; ?></td>
                            </tr>
                            <tr>
                                <td>substitute_list</td>
                                <td><?php echo $data['match']['substitute_list']; ?></td>
                            </tr>
                            <tr>
                                <td>score_data</td>
                                <td><?php echo $data['match']['score_data']; ?></td>
                            </tr>
                            <tr>
                                <td>status</td>
                                <td><?php echo $data['match']['status']; ?></td>
                            </tr>
                            <tr>
                                <td>status_overview</td>
                                <td><?php echo $data['match']['status_overview']; ?></td>
                            </tr>
                            <tr>
                                <td>match_closure_date</td>
                                <td><?php echo $data['match']['match_closure_date']; ?></td>
                            </tr>
                            <tr>
                                <td>delay_minute</td>
                                <td><?php echo $data['match']['delay_minute']; ?></td>
                            </tr>
                            <tr>
                                <td>delay_message</td>
                                <td><?php echo $data['match']['delay_message']; ?></td>
                            </tr>
                            <tr>
                                <td>delay_by_admin</td>
                                <td><?php echo $data['match']['delay_by_admin']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>

