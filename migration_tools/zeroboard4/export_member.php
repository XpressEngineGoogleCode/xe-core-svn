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
    $query = "select count(*) as count from zetyx_member_table";
    $count_result = mysql_query($query) or die(mysql_error());
    $count_info = mysql_fetch_object($count_result);
    $total_count = $count_info->count;

    // 회원정보를 구함
    $query = "select * from zetyx_member_table";
    $member_result = mysql_query($query) or die(mysql_error());

    // 헤더 정보 출력
    printf("<root type=\"%s\" count=\"%d\">", 'member', $total_count);

    $xml_buff = '';
    while($member_info = mysql_fetch_object($member_result)) {
        $member_buff = null;

        // 기본정보들
        $member_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->user_id)));
        $member_buff .= sprintf("<password>%s</password>\n", addXmlQuote($member_info->password));
        $member_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->name)));
        $member_buff .= sprintf("<email_address>%s</email_address>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->email)));
        $member_buff .= sprintf("<homepage>%s</homepage>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->homepage)));
        $member_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->name)));
        $member_buff .= sprintf("<birthday>%s</birthday>\n", date('YmdHis', $member_info->birth));
        $member_buff .= sprintf("<regdate>%s</regdate>\n", date('YmdHis', $member_info->reg_date));
	if($member_info->mailing!=0) $allow_mailing = 'Y';
	else $allow_mailing = 'N';
	$member_buff .= sprintf("<allow_mailing>%s</allow_mailing>\n", $allow_mailing);
	$member_buff .= sprintf("<point>%d</point>\n", $member_info->point1+$member_info->point2);

        // 이미지네임
        $image_nickname_file = sprintf('%s%d.gif',$image_nickname_path,$member_info->no);
        if(file_exists($image_nickname_file)) $member_buff .= sprintf("<image_nickname>%s</image_nickname>\n", getFileContentByBase64Encode($image_nickname_file));

        // 이미지마크
        $image_mark_file = sprintf('%s%d.gif',$image_mark_path,$member_info->no);
        if(file_exists($image_mark_file)) $member_buff .= sprintf("<image_mark>%s</image_mark>\n", getFileContentByBase64Encode($image_mark_file));

    
        $xml_buff .= sprintf("<member user_id=\"%s\">\n%s</member>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->user_id)), $member_buff);
        print $xml_buff;
    }

    print '</root>";
?>
