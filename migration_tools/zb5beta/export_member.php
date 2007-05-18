<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // 이미지닉네임, 이미지마크 경로 구함
    $image_nickname_path = sprintf('%s/icon/private_name/',$path);
    $image_mark_path = sprintf('%s/icon/private_icon/',$path);

    // 다운로드 헤더 출력
    printDownloadHeader($filename);

    // 회원의 수를 구함
    $query = sprintf("select count(*) as count from %smember_list", $db_prefix);
    $count_result = mysql_query($query) or die(mysql_error());
    $count_info = mysql_fetch_object($count_result);
    $total_count = $count_info->count;

    // 회원정보를 구함
    $query = sprintF("select * from %smember_list", $db_prefix);
    $member_result = mysql_query($query) or die(mysql_error());

    // 헤더 정보 출력
    printf("<root type=\"%s\" count=\"%d\">", 'member', $total_count);

    $xml_buff = '';
    while($member_info = mysql_fetch_object($member_result)) {
        $member_buff = null;

        // 기본정보들
        $member_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote($member_info->user_id));
        $member_buff .= sprintf("<password>%s</password>\n", addXmlQuote($member_info->passwd));
        $member_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote($member_info->user_name));
        $member_buff .= sprintf("<email_address>%s</email_address>\n", addXmlQuote($member_info->email_address));
        $member_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote($member_info->nick_name));
        $member_buff .= sprintf("<regdate>%s</regdate>\n", $member_info->regdate);
	    $member_buff .= sprintf("<allow_mailing>%s</allow_mailing>\n", $member_info->mailing);
	    $member_buff .= sprintf("<point>%d</point>\n", $member_info->point);
        if($member_info->sign) $member_buff .= sprintf("<signature>%s</signature>\n", base64_encode($member_info->sign));

        // 이미지네임
        if($member_info->image_nick) {
            $pos = strpos($member_info->image_nick, 'files');
            $image_nickname_file = sprintf('%s/%s', $path, substr($member_info->image_nick, $pos, strlen($member_info->image_nick)));
            if(file_exists($image_nickname_file)) $member_buff .= sprintf("<image_nickname>%s</image_nickname>\n", getFileContentByBase64Encode($image_nickname_file));
        }

        // 이미지마크
        if($member_info->image_mark) {
            $pos = strpos($member_info->image_mark, 'files');
            $image_mark_file = sprintf('%s/%s', $path, substr($member_info->image_mark, $pos, strlen($member_info->image_mark)));
            if(file_exists($image_mark_file)) $member_buff .= sprintf("<image_mark>%s</image_mark>\n", getFileContentByBase64Encode($image_mark_file));
        }
    
        printf("<member user_id=\"%s\">\n%s</member>\n", addXmlQuote($member_info->user_id), $member_buff);
    }

    print '</root>';
?>
