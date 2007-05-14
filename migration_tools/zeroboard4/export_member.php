<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // 백업할 대상 컬럼을 지정
    $target_column = array(
        'user_id' => 'user_id',
        'password' => 'password',
        'name' => 'user_name',
        'email' => 'email_address',
        'homepage' => 'homepage',
    );

    // 이미지닉네임, 이미지마크 경로 구함
    $image_nickname_path = sprintf('%s/icon/private_name/',$path);
    $image_mark_path = sprintf('%s/icon/private_icon/',$path);

    // 회원정보를 구함
    $query = "select * from zetyx_member_table";
    $member_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    while($member_info = mysql_fetch_object($member_result)) {
        $member_buff = null;

        // 일단 기본 데이터만 추가
        foreach($target_column as $key => $val) {
            $member_buff .= sprintf("<%s>%s</%s>", $val, addXmlQuote($member_info->{$key}), $val);
        }

        // 닉네임, 생일, 등록일등을 추가 등록
        $member_buff .= sprintf("<nick_name>%s</nick_name>", addXmlQuote($member_info->name));
        $member_buff .= sprintf("<birthday>%s</birthday>", date("YmdHis", $member_info->birth));
        $member_buff .= sprintf("<regdate>%s</regdate>", date("YmdHis", $member_info->regdate));

        // 이미지네임
        $image_nickname_file = sprintf('%s%d.gif',$image_nickname_path,$member_info->no);
        if(file_exists($image_nickname_file)) $member_buff .= sprintf("<image_nickname>%s</image_nickname>", getFileContentByBase64Encode($image_nickname_file));

        // 이미지마크
        $image_mark_file = sprintf('%s%d.gif',$image_mark_path,$member_info->no);
        if(file_exists($image_mark_file)) $member_buff .= sprintf("<image_mark>%s</image_mark>", getFileContentByBase64Encode($image_mark_file));

    
        $xml_buff .= sprintf('<member user_id="%s">%s</member>', addXmlQuote($member_info->user_id), $member_buff);
    }

    $xml_buff = sprintf("<zeroboard4>%s</zeroboard4>", $xml_buff);

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
