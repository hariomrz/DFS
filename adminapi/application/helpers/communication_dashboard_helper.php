<?php


/**
*below Function for update email_balance via hashtable
*/
function email_bal_update($row,$post)
{
	$row["email_balance"] = $row["email_balance"]+ $post['value'];
	return $row;
}
function notification_bal_update($row,$post)
{
	$row["notification_balance"] = $row["notification_balance"]+ $post['value'];
	return $row;
}