<?php
    /**
     * @file   modules/trackback/lang/en.lang.php
     * @author NHN (developers@xpressengine.com)
     * @brief  trackback module / basic language pack
     **/
	$lang->cmd_toggle_checked_trackback = 'Reverse the selected trackback(s)';
    $lang->cmd_delete_checked_trackback = 'Delete the selected trackback(s)';

    $lang->msg_cart_is_null = 'Please select a trackback to delete.';
    $lang->msg_checked_trackback_is_deleted = '%d trackback(s) deleted.';

	$lang->send_trackback_url = 'Destination URL';
    $lang->msg_trackback_url_is_invalid = 'Destination URL is invalid';
    $lang->msg_trackback_send_success = 'Sent successfully';
	$lang->msg_trackback_send_failed = 'Failed to send';

    $lang->search_target_list = array(
        'url' => 'Target URL',
        'blog_name' => 'Target Site Name',
        'title' => 'Title',
        'excerpt' => 'Excerpt',
        'regdate' => 'Posted Date',
        'ipaddress' => 'IP Address',
    );

    $lang->enable_trackback = "Use Trackback";
	$lang->about_enable_trackback = "When it is unchecked, all the trackback collection on the site will be stopped.";
	$lang->no_trackbacks = 'No Trackbacks';
?>
