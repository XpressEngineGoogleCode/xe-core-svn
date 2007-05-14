<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // 이미지닉네임, 이미지마크 경로 구함
    $image_nickname_path = sprintf('%s/icon/private_name/',$path);
    $image_mark_path = sprintf('%s/icon/private_icon/',$path);

    // 회원정보를 구함
    $query = "select * from zb_member_list";
    $member_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    while($member_info = mysql_fetch_object($member_result)) {
        $member_buff = null;

        // 기본정보들
        $member_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($member_info->user_id));
        $member_buff .= sprintf('<password>%s</password>', addXmlQuote($member_info->password));
        $member_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($member_info->user_name));
        $member_buff .= sprintf('<email_address>%s</email_address>', addXmlQuote($member_info->email_address));
        $member_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($member_info->nick_name));
        $member_buff .= sprintf('<regdate>%s</regdate>', $member_info->reg_date);
        if($member_info->sign) $member_buff .= sprintf('<signature><![CDATA[%s]]></signature>', $member_info->sign);

        // 이미지네임
        if($member_info->image_nick) {
            $pos = strpos($member_info->image_nick, 'files');
            $image_nickname_file = sprintf('%s/%s', $path, substr($member_info->image_nick, $pos, strlen($member_info->image_nick)));
            if(file_exists($image_nickname_file)) $member_buff .= sprintf('<image_nickname>%s</image_nickname>', getFileContentByBase64Encode($image_nickname_file));
        }

        // 이미지마크
        if($member_info->image_mark) {
            $pos = strpos($member_info->image_mark, 'files');
            $image_mark_file = sprintf('%s/%s', $path, substr($member_info->image_mark, $pos, strlen($member_info->image_mark)));
            if(file_exists($image_mark_file)) $member_buff .= sprintf('<image_mark>%s</image_mark>', getFileContentByBase64Encode($image_mark_file));
        }
    
        $xml_buff .= sprintf('<member user_id="%s">%s</member>', addXmlQuote($member_info->user_id), $member_buff);
    }

    $xml_buff = sprintf('<root type="zb5beta">%s</root>', $xml_buff);

    // 다운로드
    procDownload($filename, $xml_buff);
?>
