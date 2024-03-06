<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Wall_model extends Common_Model
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * [addMedia Used to insert media info in database]
     * @param [array] $data [media info]
     */
    function addMedia($data)
    {
        $this->db->insert(MEDIA, $data);
        $insert_id = $this->db->insert_id();
        $this->load->model('vsocial_model');
        $this->vsocial_model->checkMediaCounts($data, true);
        return $insert_id;
    }

    /**
     * [updateMedia Used to update media info in database]
     * @param [array] $data [media info]
     */
    function updateMedia($Media, $EntityID, $UserID = 0, $ModuleID = 1, $ModuleEntityID = 0)
    {
        $update = array();
        if ($Media)
        {
            foreach ($Media as $m)
            {

                $u = array('UserID'=>$UserID,'MediaGUID' => $m['GuID'],
                    'MediaSectionReferenceID' => $EntityID, 'StatusID' => '2',
                    'Caption' => $m['Caption'], 'AlbumID' => getAlbumID($UserID, 'Timeline Photos', $ModuleID, $ModuleEntityID));
                $update[] = $u;
            }
            $this->db->update_batch(MEDIA, $update, 'MediaGUID');
        }
    }

    /**
     * [getMedia used to get media name]
     * @param  [int] $SourceId [description]
     * @return [array]       [media name]
     */
    function getMedia($SourceId)
    {
        $this->db->select('ImageName');
        $this->db->from(MEDIA);
        $this->db->where('SourceId', $SourceId);
        $sql = $this->db->get();
        return $sql->result_array();
    }
    
    function sendNotificationUser($ModuleID, $ModuleEntityID, $PostContent, $UserID, $ActivityID, $StatusID)
    {
        //$NotificationUserList = $this->notification_model->getNotificationUserList($ActivityID,$UserID);
        $NotificationUserList = $this->notification_model->getFollowers($UserID);

        //print_r($NotificationUserList);die;
        $usrs = array($UserID);
        $tagged = array();
        preg_match_all('/{{([0-9]+)}}/', $PostContent, $matches);
        if (!empty($matches[1]))
        {
            foreach ($matches[1] as $key => $match)
            {
                if ($match != $UserID && !in_array($match, $usrs))
                {
                    $usrs[] = $match;
                    $tagged[] = $match;
                }
                /* if($match==$UserID){
                  unset($usrs[$key]);
                  unset($tagged[$key]);
                  } */
            }
        }

        if (!empty($tagged))
        {
            $TagParameters[0]['ReferenceID'] = $UserID;
            $TagParameters[0]['Type'] = 'User';
            $TagParameters[1]['ReferenceID'] = $ActivityID;
            $TagParameters[1]['Type'] = 'Activity';
            if ($ModuleID == 21)
            {
                $notificationTypeID = 59;
            }
            else if ($ModuleID == 22)
            {
                $notificationTypeID = 60;
            }
            $this->notification_model->addNotification($notificationTypeID, $UserID, $tagged, $ActivityID, $TagParameters);
        }

        if ($NotificationUserList)
        {

            switch ($ModuleID)
            {
                case 21:
                    $notificationTypeID = 56;
                    break;
                case 22:

                    $notificationTypeID = 57;
                    break;
                case 23:
                case 24:
                    $notificationTypeID = 58;
                    break;
            }

            if ($ModuleID == 22 && ($ModuleEntityID > 0 && $ModuleEntityID != ''))
            {
                // No need to send notification if chat is created from monthly competition 
            }
            else
            {
                $parameters[0]['ReferenceID'] = $UserID;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $ActivityID;
                $parameters[1]['Type'] = 'Activity';
                foreach ($NotificationUserList as $key => $val)
                {
                    if (in_array($val, $tagged))
                    {
                        unset($NotificationUserList[$key]);
                    }
                }
                if ($StatusID == 2)
                {
                    $this->notification_model->addNotification($notificationTypeID, $UserID, $NotificationUserList, $ActivityID, $parameters);
                }
            }
        }
    }

    function add_activity_tag($Tags, $ActivityID)
    {
        foreach ($Tags as $tag)
        {
            $tag = (string) ucfirst($tag);
            $TagData = $this->db->query("SELECT TagID FROM " . TAGS . " WHERE Name = " . $this->db->escape($tag) . " AND StatusID = 2");
            if ($TagData->num_rows() > 0)
            {
                $getTag = $TagData->row_array();
            }
            else
            {
                $TagMasterArr = array(
                    'Name' => $tag,
                    'CreatedDate' => getCurrentDate('%Y-%m-%d %H:%i:%s')
                );
                $this->db->insert(TAGS, $TagMasterArr);
                $getTag['TagID'] = $this->db->insert_id();
            }
            $TagArr = array(
                'TagID' => $getTag['TagID'],
                'ActivityID' => $ActivityID
            );
            $this->db->insert(ACTIVITYTAG, $TagArr);
        }
    }

    /**
     * [Update child activity id in media table]
     * @param  [int] $MediaReferenceID 	[MediaReferenceID]
     * @param  [int] $MediaGUID         [MediaGUID]
     * @return NULL
     */
    function update_media_referenceID($MediaReferenceID = '', $MediaGUID = '')
    {
        if ($MediaReferenceID != '' && $MediaGUID != '')
        {
            $this->db->where('MediaGUID', $MediaGUID);
            $this->db->set('MediaReferenceID', $MediaReferenceID);
            $this->db->update('Media');
        }
    }

    /**
     * [getWallPostListCount get total number of post]
     * @param  [array] $Data   	[Post details]
     * @param  [int] $UserID 	[User id]
     * @return [int]         	[total number of post]
     */
    function getWallPostListCount($Data, $UserID)
    {
        $this->db->select("SQL_CALC_FOUND_ROWS P.PostID, PU.ProfilePicture, P.PostGuID, PT.PostType, P.UserID, P.PostContent, P.PostCommentCount, P.PostLikeCount, P.StatusID, P.CreatedDate, P.PostID, CONCAT_WS( ' ', PU.FirstName, PU.LastName ) as Name");
        $this->db->from(POST . ' as P');
        $this->db->join(USERS . ' as PU', 'PU.UserID=P.UserID', 'INNER');
        $this->db->join(POSTTYPES . ' as PT', 'PT.PostTypeID=P.PostTypeID', 'INNER');
        $this->db->where(array('P.PostTypeID' => $Data['PostType'], 'P.StatusID' => 2, 'P.EntityID' => $Data['checkentity']));
        $sql = $this->db->get();
        return $sql->num_rows();
    }

    function getWallPostList($Data, $UserID, $isGroupOwner, $PageNo = PAGE_NO, $PageSize = PAGE_SIZE)
    {
        $Query = $this->db->query("SELECT  SQL_CALC_FOUND_ROWS P.PostID,PU.ProfilePicture ,  P.PostGuID, PT.PostType, P.UserID, P.PostContent, 
				P.PostCommentCount, P.PostLikeCount, P.StatusID, P.CreatedDate, P.PostID, 
				CONCAT(PU.FirstName, ' ',PU.LastName) as Name FROM " . POST . " as P INNER JOIN " . USERS . " as 
				PU on PU.UserID=P.UserID INNER JOIN " . POSTTYPES . " as PT on PT.PostTypeID=P.PostTypeID 
				where P.PostTypeID = " . $this->db->escape($Data['PostType']) . " and P.StatusID = 2 and EntityID=" . $this->db->escape($Data['checkentity']) . " order 
				by PostID  desc limit " . $this->getOffset($PageNo, $PageSize) . "," . $PageSize . "");
        if ($Query->num_rows() > 0)
        {
            foreach ($Query->result_array() as $Wall)
            {
                //wall media start
                $wallMedia = $this->wall_model->getMedia($Wall['PostID']);
                foreach ($wallMedia as $wallimage)
                {
                    $wallimage['BigImageName'] = get_full_path('wall_image', '', $wallimage['ImageName'], '1000', '1000', '36');
                    $wallimage['ImageName'] = get_full_path('wall_image', '', $wallimage['ImageName'], '192', '192', '36');
                    $Wall['Media'][] = $wallimage;
                }

                if ($this->flag_model->checkFlagStatus($UserID, $Wall['PostGuID'], 'Post'))
                {
                    $Wall['isFlagged'] = '2';
                }
                else
                {
                    $Wall['isFlagged'] = '1';
                }

                //Temporary Disabled 
                $Wall['isFlagged'] = '0';

                $Wall['MediaCount'] = count($wallMedia);
                //wall media end
                $Wall['ProfilePicture'] = get_full_path($type = 'profile_image', '', $Wall['ProfilePicture'], $height = '192', $width = '192', $size = '192');
                $Wall['TimeStamp'] = timeDifference($Wall['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));


                $queryy = $this->db->query("select ComplimentTypeID.ComplimentType  from ComplimentTypeID inner join  PostCompliment on ComplimentTypeID.ComplimentID = PostCompliment.ComplimentType  where PostCompliment.PostID = " . $this->db->escape($Wall['PostID']) . " and UserID = " . $UserID . "   ");
                $rs = $queryy;
                if ($rs->num_rows() > 0)
                {
                    $queryy = $rs->row_array();
                    $Wall['ComplimentType'] = $queryy['ComplimentType'];
                    $setcompi = 1;
                }
                else
                {
                    $setcompi = 0;
                }
                if ($Wall['UserID'] != $UserID && $isGroupOwner == '0')
                {
                    $Wall['DeletePower'] = 'display:none;list-style:none';
                    $Wall['CanDelete'] = '0';
                    if ($setcompi == 0)
                    {
                        $Wall['Compliment'] = "1";
                        $Wall['Oopcompliment'] = "0";
                    }
                    else
                    {
                        $Wall['Compliment'] = "0";
                        $Wall['Oopcompliment'] = "0";
                        $Wall['Oopocompliment'] = "1";
                    }
                }
                else
                {
                    $Wall['DeletePower'] = 'display:block';
                    $Wall['CanDelete'] = '1';
                    if ($setcompi == 0)
                    {
                        $Wall['Compliment'] = "0";
                        $Wall['Oopcompliment'] = "1";
                        $Wall['Oopocompliment'] = "0";
                    }
                }
                $WallPostComment = $this->db->query("SELECT PU.ProfilePicture,PC.PostCommentGUID,PC.PostComment,PC.UserID,PC.CreatedDate,PC.StatusID,CONCAT(PU.FirstName, ' ',PU.LastName) as Name FROM " . POSTCOMMENTS . " as  PC INNER JOIN  " . USERS . " as PU on PU.UserID=PC.UserID WHERE  PC.PostID = '" . $this->db->escape($Wall['PostID']) . "' order by PC.PostCommentID DESC limit " . $this->getOffset($PageNo, $PageSize) . ",5");
                $Wall['Comment'] = array();
                if ($WallPostComment->num_rows() != 0)
                {
                    $Wall['PostCommentCount'] = $WallPostComment->num_rows();
                    $i = 0;
                    foreach ($WallPostComment->result_array() as $value)
                    {
                        if ($i >= 5)
                            break;
                        $i++;
                        $value['ProfilePicture'] = get_full_path($type = 'profile_image', '', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
                        $value['TimeStamp'] = timeDifference($value['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));
                        if ($value['UserID'] != $UserID && $Wall['UserID'] != $UserID && $isGroupOwner == '0')
                        {
                            $value['DeletePower'] = 'display:none;list-style:none';
                            $value['CanDelete'] = '0';
                        }
                        else
                        {
                            $value['deletePower'] = '';
                            $value['CanDelete'] = '0';
                        }
                        $Wall['Comment'][] = $value;
                    }
                    $Wall['Type'] = 'comment';
                }
                $sql = $this->db->query("select PostLikeID from PostLike where  PostID = " . $this->db->escape($Wall['PostID']) . " ")->num_rows();
                if ($sql > 1)
                {
                    $Wall['LikeStatus'] = 'Likes';
                }
                else
                {
                    $Wall['LikeStatus'] = 'Like';
                }

                if ($this->checkLike($Wall['PostGuID'], $UserID))
                {
                    $Wall['LikeStatus'] = '1';
                }
                else
                {
                    $Wall['LikeStatus'] = '0';
                }
                $queryrz = $this->db->query("select count(PostComplimentID) as compliments from PostCompliment where PostID = " . $this->db->escape($Wall['PostID']) . "")->row_array();
                $Wall['Compliments'] = $queryrz['compliments'];

                preg_match_all("{{([0-9]+)}}", $Wall['PostContent'], $matches);
                //print_r($matches[1]);

                if (!empty($matches[1]))
                {
                    foreach ($matches[1] as $match)
                    {
                        $UserName = $this->get_user_data($match);
                        $UserName = '<a href="#">' . $UserName['FirstName'] . ' ' . $UserName['LastName'] . '</a>';
                        $Wall['PostContent'] = str_replace('{{' . $match . '}}', $UserName, $Wall['PostContent']);
                    }
                }

                $Wall['CommentPageNo'] = 2;
                $Wall['PostCommentShow'] = 1;
                //$PostContent = preg_match('\{{(.*?)\}}',$Wall['PostContent'],$match);
                //if(!empty($match)){
                //	$Wall['PostContent'] = 'flsmfs';
                //}
                //$Wall['PostContent'] = 'Hey';
                $row[] = $Wall;
            }
            return $row;
        }
        else
        {
            return false;
        }
    }

    function getProfileWithUserID($UserID)
    {
        $sql = $this->db->query("select ProfilePicture, FirstName , LastName from Users where UserID = " . $UserID . " ");
        if ($sql->num_rows() > 0)
        {
            return $sql->row_array();
        }
        else
        {
            return false;
        }
    }

    function do_like($PostGuID, $UserID)
    {
        $Data = array(
            'PostID' => $this->login_model->GetPostID($PostGuID),
            'UserID' => $UserID,
            'CreatedDate' => getCurrentDate('%Y-%m-%d %H:%i:%s')
        );
        $this->login_model->addEdit(POSTLIKE, $Data);
    }

    function remove_like($PostGuID, $UserID)
    {
        $this->db->query('DELETE  FROM ' . POSTLIKE . ' WHERE PostID="' . $this->login_model->GetPostID($PostGuID) . '" AND UserID="' . $UserID . '"');
    }

    function checkLike($PostID, $UserID)
    {
        $PostLike = $this->db->query("SELECT PostID FROM " . POSTLIKE . " WHERE 
			PostID='" . $this->login_model->GetPostID($PostID) . "' AND UserID='" . $UserID . "' LIMIT 1");
        return $PostLike->num_rows();
    }

    function addComment($Data)
    {
        $this->login_model->addEdit(POSTCOMMENTS, $Data);
    }

    function getComment($LoginSessionKey, $UserID, $PostID, $PostComment, $PostCommentGUID)
    {
        $sql = $this->db->query("SELECT ProfilePicture,CONCAT(Users.FirstName, '  ',Users.LastName) as Name from " . ACTIVELOGINS . " left join " . USERS . " on Users.UserID=ActiveLogins.UserID where ActiveLogins.LoginSessionKey = " . $this->db->escape($LoginSessionKey) . "");
        if ($sql->num_rows() > 0)
        {
            $result = $sql->row_array();
            $getpath = get_full_path($type = 'profile_image', '', $result['ProfilePicture'], $height = '192', $width = '192', $size = '192');
            $WallPostComment = $this->db->query("SELECT PC.UserID FROM " . POSTCOMMENTS . " as  PC WHERE  PC.PostID = " . $this->db->escape($PostID) . "");
            $Data['PostCommentCount'] = $WallPostComment->num_rows();
            $res = array("CreatedDate" => getCurrentDate('%Y-%m-%d %H:%i:%s'),
                "Name" => $result['Name'],
                "PostComment" => $PostComment,
                "PostCommentGUID" => $PostCommentGUID,
                "ProfilePicture" => $getpath,
                "StatusID" => "2",
                "TimeStamp" => timeDifference(getCurrentDate('%Y-%m-%d %H:%i:%s'), getCurrentDate('%Y-%m-%d %H:%i:%s')),
                "UserID" => $UserID,
                "DeletePower" => "",
                "CanDelete" => "1"
            );

            $Data['Comment'] = $res;
            return $Data;
        }
        else
        {
            return false;
        }
    }

    function getCommentCount($PostGuID)
    {
        $sql = $this->db->query("SELECT PC.UserID FROM " . POSTCOMMENTS . " as  PC left join " . ACTIVITY . " on " . ACTIVITY . ".ActivityID=PC.EntityID WHERE  " . ACTIVITY . ".EntityID = '" . $this->login_model->GetPostID($PostGuID) . "'");
        return $sql->num_rows() - 1;
    }

    function deleteGroupComment($isGroupOwner, $PostGuID, $UserID)
    {
        if ($isGroupOwner == '0')
        {
            $query = "delete pc from " . POSTCOMMENTS . " pc left join " . POST . " p on pc.EntityID=p.PostID and pc.PostCommentGUID = '" . $PostGuID . "' where pc.EntityType='Post' AND (pc.PostCommentGUID = '" . $PostGuID . "' and pc.UserID = " . $UserID . ") or (pc.PostCommentGUID = '" . $PostGuID . "' and p.UserID = " . $UserID . ")";
        }
        else
        {
            $query = "delete from " . POSTCOMMENTS . " where PostCommentGUID = '" . $PostGuID . "'";
        }
        $sql = $this->db->query($query);
    }

    function deleteUserComment($PostGuID, $UserID)
    {
        $query = "delete from " . POSTCOMMENTS . " where PostCommentGUID = '" . $PostGuID . "' and UserID = " . $UserID . "";
        $sql = $this->db->query($query);
    }

    function deleteGroupPost($isGroupOwner, $PostGuID, $UserID)
    {
        if ($isGroupOwner == '0')
        {
            $sql = "update Post set StatusID = 3  where PostGuID ='" . $PostGuID . "' and UserID = " . $UserID . " ";
        }
        else
        {
            $sql = "update Post set StatusID = 3  where PostGuID ='" . $PostGuID . "'";
        }
        if ($this->db->query($sql))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function deleteUserPost($PostGuID, $UserID)
    {
        if ($this->db->query("update Post set StatusID = 3  where PostGuID ='" . $PostGuID . "' and UserID = " . $UserID . " "))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function seeAllPostCommentsCount($PostGuID)
    {
        $sql = $this->db->query("SELECT PU.ProfilePicture,PC.PostCommentGUID,PC.PostComment,PC.UserID,PC.CreatedDate,PC.StatusID,CONCAT(PU.FirstName, ' ',PU.LastName) as Name FROM " . POSTCOMMENTS . " as  PC INNER JOIN  " . USERS . " as PU on PU.UserID=PC.UserID left join " . ACTIVITY . " on " . ACTIVITY . ".ActivityID=" . POSTCOMMENTS . ".EntityID WHERE  " . ACTIVITY . ".ActivityID = '" . $this->login_model->GetPostID($PostGuID) . "'");
        return $sql->num_rows();
    }

    function seeAllPostComments($PostGuID, $UserID, $PageNo = PAGE_NO, $PageSize = PAGE_SIZE)
    {
        $WallPostComment = $this->db->query("SELECT PU.ProfilePicture,PC.PostCommentGUID,PC.PostComment,PC.UserID,PC.CreatedDate,PC.StatusID,CONCAT(PU.FirstName, ' ',PU.LastName) as Name FROM " . POSTCOMMENTS . " as  PC INNER JOIN  " . USERS . " as PU on PU.UserID=PC.UserID left join " . ACTIVITY . " on " . ACTIVITY . ".ActivityID=" . POSTCOMMENTS . ".EntityID WHERE  " . ACTIVITY . ".ActivityID = '" . $this->login_model->GetPostID($PostGuID) . "' order by PC.PostCommentID DESC limit " . $this->getOffset($PageNo, $PageSize) . "," . $PageSize);
        $Wall['comment'] = '';
        if ($WallPostComment->num_rows() != 0)
        {
            foreach ($WallPostComment->result_array() as $value)
            {
                $isGroupOwner = 0;
                $getpath = get_full_path($type = 'profile_image', '', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');


                if ($value['UserID'] != $UserID && $isGroupOwner == '0')
                {
                    $deletePower = 'display:none;list-style:none';
                }
                else
                {
                    $deletePower = 'display:block;';
                }

                if ($UserID == $value['UserID'])
                {
                    $CanDelete = '1';
                }
                else
                {
                    $CanDelete = '0';
                }

                $res = array("CreatedDate" => $value['CreatedDate'],
                    "Name" => $value['Name'],
                    "PostComment" => $value['PostComment'],
                    "PostCommentGUID" => $value['PostCommentGUID'],
                    "ProfilePicture" => $getpath,
                    "StatusID" => "2",
                    "TimeStamp" => timeDifference($value['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s')),
                    "UserID" => $UserID,
                    "deletePower" => '0',
                    "CanDelete" => $CanDelete
                );
                $Wall['Comment'][] = $res;
            }
            return $Wall;
        }
        else
        {
            return false;
        }
    }

    function getWallPostComments($PostGuID, $PageSize = PAGE_SIZE, $PageNo = PAGE_NO)
    {
        /* GetWallPostComments Data query  - starts */
        $SQL = "SELECT PC.PostCommentGUID,PC.PostComment,PC.CreatedDate,PC.CreatedDate,CONCAT(U.FirstName, U.LastName) as  Name,U.UserID 
		FROM " . POSTCOMMENTS . " as PC 
		INNER JOIN  " . POST . " as P ON P.PostID= PC.EntityID
		INNER JOIN	" . USERS . " as U ON U.UserID=PC.UserID
		WHERE P.PostGuID=" . $this->db->escape($PostGuID) . " AND EntityType='Post' ";
        if ($PageNo != '' && $PageSize)
            $SQL .= " LIMIT " . $PageNo . " , " . $PageSize . " ";
        /* GetWallPostComments Data query  - end */
        $QUERY = $this->db->query($SQL);
        if ($QUERY->num_rows() != 0)
        {
            foreach ($QUERY->result_array() as $UserData)
            {
                $User['PostCommentGUID'] = $UserData['PostCommentGUID'];
                $User['PostComment'] = $UserData['PostComment'];
                $User['UserID'] = $UserData['UserID'];
                $User['Name'] = $UserData['Name'];
                $User['ProfilePictureURL'] = '';
                $User['TimeStamp'] = timeDifference($UserData['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));
                $WallPostCommentsData[] = $User;
            }
            return $WallPostCommentsData;
        }
        else
        {
            return false;
        }
    }

    function getWallPostDetail($PostGuID, $PageNo = PAGE_NO, $PageSize = PAGE_SIZE)
    {

        $Query = $this->db->query("SELECT P.PostGuID, PT.PostType, P.UserID, P.PostContent, P.PostCommentCount, P.PostLikeCount, P.StatusID, P.CreatedDate, P.PostID,
			CONCAT(PU.FirstName,PU.LastName) as Name FROM " . POST . " as P INNER JOIN " . USERS . " as PU on PU.UserID=P.UserID  INNER JOIN " . POSTTYPES . " as PT on 
			PT.PostTypeID=P.PostTypeID WHERE  PostGuID = ".$this->db->escape($PostGuID)."  LIMIT 1 ");
        if ($Query->num_rows() != 0)
        {
            foreach ($Query->result_array() as $Wall)
            {
                $Wall['TimeStamp'] = timeDifference($Wall['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));
                $row = $Wall;
            }
            $WallPost = $row;
            $Content = $WallPost;
        }
        $WallPostMedia = $this->db->query("SELECT PostMediaGUID,PostMediaOrgName,PostMediaName,PostMediaSize,CreatedDate,StatusID 
			FROM  " . POSTMEDIA . "  WHERE  PostID = '" . $this->login_model->GetPostID($PostGuID) . "' ");
        if ($WallPostMedia->num_rows() != 0)
        {
            foreach ($WallPostMedia->result_array() as $Wall)
            {
                $Wall['TimeStamp'] = timeDifference($Wall['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));

                $row = $Wall;
            }
            $PostMedia[] = $row;
            $Content['WallPostMedia'] = $PostMedia;
        }
        $WallPostComment = $this->db->query("SELECT PC.PostCommentGUID,PC.PostComment,PC.UserID,PC.CreatedDate,PC.StatusID,CONCAT(PU.FirstName, PU.LastName) as 
			Name FROM " . POSTCOMMENTS . " as  PC INNER JOIN  " . USERS . " as PU on PU.UserID=PC.UserID WHERE  EntityID = '" . $this->login_model->GetPostID($PostGuID) . "' ");
        if ($WallPostComment->num_rows() != 0)
        {
            foreach ($WallPostComment->result_array() as $Wall)
            {
                $Wall['TimeStamp'] = timeDifference($Wall['CreatedDate'], getCurrentDate('%Y-%m-%d %H:%i:%s'));
                $row = $Wall;
            }
            $PostComment[] = $row;
            $Content['Comments'] = $PostComment;
        }
        return $Content;
    }

    function getMemberWallPost($GroupID)
    {
        $row = array();
        $value = array();
        $sql = "SELECT Users.FirstName,Users.LastName , Users.ProfilePicture FROM `GroupMembers` inner join Users on GroupMembers.UserID = Users.UserID where GroupMembers.GroupID = " . $this->db->escape($GroupID) . "";
        $Return['totalrows'] = $this->db->query($sql)->num_rows();
        $res = $this->db->query($sql);
        if ($Return['totalrows'] > 0)
        {
            foreach ($res->result_array() as $value)
            {
                if ($value['ProfilePicture'] != 'default-148.png' && $value['ProfilePicture'] != '')
                {
                    $value['ProfilePicture'] = get_full_path($type = 'profile_image', '', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
                    $row[] = $value;
                }
                else
                {
                    $value['ProfilePicture'] = site_url() . "/assets/img/no-user-thumb.png"; // get_full_path($type = 'profile_image','', $value['ProfilePicture'], $height = '192', $width = '192', $size = '192');
                    $row[] = $value;
                }
            }
        }

        return $row;
    }

    function countLikeMember($PostGuID)
    {

        /* Delete POST query  - starts */
        $query = "SELECT Users.FirstName,Users.LastName,Users.ProfilePicture from " . USERS . " inner join 
		" . POSTLIKE . " on Users.UserID = PostLike.UserID where 
		PostLike.PostID=" . $this->login_model->GetPostID($PostGuID);
        $sql = $this->db->query($query);
        $UserData = array();
        if ($sql->num_rows() > 0)
        {
            foreach ($sql->result_array() as $key => $value)
            {
                $value['ProfilePicture'] = get_full_path('profile_image', '', $value['ProfilePicture'], '36', '36', '36');
                $UserData[] = $value;
            }
        }
        $Return['Data'] = $UserData;
        $Return['totalrecords'] = $sql->num_rows();
        /* Delete POST query  - end */
    }

    function setCompliment($PostGuID, $UserID, $ComplimentType)
    {
        /* Delete POST query  - starts */
        $delquery = "delete from PostCompliment where PostID=" . $this->login_model->GetPostID($PostGuID) . " and
		UserID=" . $UserID;
        $delsql = $this->db->query($delquery);

        $query = "insert into PostCompliment(PostID,UserID,ComplimentType) 
		values(" . $this->login_model->GetPostID($PostGuID) . "," . $UserID . "," . $ComplimentType . ") ";
        $sql = $this->db->query($query);
        $Return['Data'] = $sql;

        /* Delete POST query  - end */
    }

    function countCompliment()
    {
        /* Delete POST query  - starts */
        $query = "SELECT Users.FirstName,Users.Lastname,Users.ProfilePicture from Users inner join PostCompliment on Users.UserID = PostCompliment.UserID where PostCompliment.PostID =" . $this->login_model->GetPostID($PostGuID);
        $sql = $this->db->query($query);
        return $sql->result_array();
        /* Delete POST query  - end */
    }

    function getPostCommentOtherUsers($PostID, $UserID = 0, $EntityType = 'Activity')
    {
        $sql = $this->db->query('SELECT UserID FROM ' . POSTCOMMENTS . ' WHERE EntityID=' . $this->db->escape($PostID) . ' AND EntityType = \'' . $EntityType . '\' AND UserID!=' . $UserID . " GROUP BY UserID");
        if ($sql->num_rows())
        {
            $users = array();
            foreach ($sql->result() as $user)
            {
                $users[] = $user->UserID;
            }
            return $users;
        }
        else
        {
            return array();
        }
    }

    function getPostOwner($PostGuID)
    {
        $this->db->where('PostGuID', $PostGuID);
        $sql = $this->db->get(POST);
        if ($sql->num_rows())
        {
            $data = $sql->row();
            $d['PostID'] = $data->PostID;
            $d['PostOwner'] = $data->UserID;
            return $d;
        }
        else
        {
            return false;
        }
    }

    function getMediaSizeID($size)
    {
        $query = $this->db->query("SELECT MediaSizeID FROM MediaSizes WHERE " . $this->db->escape($size) . " BETWEEN MinSize and MaxSize");
        if ($query->num_rows())
        {
            return $query->row()->MediaSizeID;
        }
        else
        {
            return '0';
        }
    }

}

?>