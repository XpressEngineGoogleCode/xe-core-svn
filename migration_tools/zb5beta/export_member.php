<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // 이미지닉네임, 이미지마크 경로 구함
    $image_nickname_path = sprintf('%s/icon/private_name/',$path);
    $image_mark_path = sprintf('%s/icon/private_icon/',$path);

    // 회원정보를 구함
    $query = "select * from zetyx_member_table";
    $member_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    while($member_info = mysql_fetch_object($member_result)) {
        $member_buff = null;

        // 기본정보들
        $member_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->user_id)));
        $member_buff .= sprintf('<password>%s</password>', addXmlQuote($member_info->password));
        $member_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->name)));
        $member_buff .= sprintf('<email_address>%s</email_address>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->email)));
        $member_buff .= sprintf('<homepage>%s</homepage>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->homepage)));
        $member_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->name)));
        $member_buff .= sprintf('<birthday>%s</birthday>', date('YmdHis', $member_info->birth));
        $member_buff .= sprintf('<regdate>%s</regdate>', date('YmdHis', $member_info->reg_date));

        // 이미지네임
        $image_nickname_file = sprintf('%s%d.gif',$image_nickname_path,$member_info->no);
        if(file_exists($image_nickname_file)) $member_buff .= sprintf('<image_nickname>%s</image_nickname>', getFileContentByBase64Encode($image_nickname_file));

        // 이미지마크
        $image_mark_file = sprintf('%s%d.gif',$image_mark_path,$member_info->no);
        if(file_exists($image_mark_file)) $member_buff .= sprintf('<image_mark>%s</image_mark>', getFileContentByBase64Encode($image_mark_file));

    
        $xml_buff .= sprintf('<member user_id="%s">%s</member>', addXmlQuote(iconv('EUC-KR','UTF-8',$member_info->user_id)), $member_buff);
    }

    $xml_buff = sprintf('<root type="zeoboard4">%s</root>', $xml_buff);

    // 다운로드
    header("Content-Type: application/octet-stream");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Content-Length: " .strlen($xml_buff));
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header("Content-Transfer-Encoding: binary");

    print $xml_buff; 
?>
