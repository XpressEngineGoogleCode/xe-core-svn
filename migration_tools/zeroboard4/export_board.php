<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // id를 구함
    $id = ereg_replace('^module\_','',$target_module);

    // 게시물을 구함
    $query = sprintf('select a.*, b.user_id from zetyx_board_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.headnum < 0 and a.arrangenum >=0 order by a.headnum, a.arrangenum', $id);
    $document_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    while($document_info = mysql_fetch_object($document_result)) {
        $document_buff = null;

        // 기본 정보
        if($document_info->headnum <= -2000000000) $document_buff .= sprintf('<is_notice>Y</is_notice>');
        if($document_buff->is_secret) $document_buff .= sprintf('<is_secret>Y</is_secret>');
        $document_buff .= sprintf('<title>%s</title>', addXmlQuote($document_info->subject));
        $document_buff .= sprintf('<readed_count>%d</readed_count>', $document_info->hit);
        $document_buff .= sprintf('<voted_count>%d</voted_count>', $document_info->vote);
        $document_buff .= sprintf('<comment_count>%d</comment_count>', $document_info->total_comment);
        $document_buff .= sprintf('<password>%s</password>', addXmlQuote($document_info->password));

        $document_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($document_info->user_id));
        if($document_info->user_id) $document_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($document_info->name));
        $document_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($document_info->name));
        $document_buff .= sprintf('<email_address>%s</email_address>', addXmlQuote($document_info->email));
        $document_buff .= sprintf('<homepage>%s</homepage>', addXmlQuote($document_info->homepage));
        $document_buff .= sprintf('<regdate>%s</regdate>', date("YmdHis", $document_info->reg_date));
        $document_buff .= sprintf('<ipaddress>%s</ipaddress>', date("YmdHis", $document_info->ip));
        $document_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($document_info->user_id));
        $document_buff .= sprintf('<password>%s</password>', addXmlQuote($document_info->password));
        $document_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($document_info->name));
        $document_buff .= sprintf('<email_address>%s</email_address>', addXmlQuote($document_info->email));
        $document_buff .= sprintf('<homepage>%s</homepage>', addXmlQuote($document_info->homepage));
        $document_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($document_info->name));
        $document_buff .= sprintf('<birthday>%s</birthday>', date('YmdHis', $document_info->birth));
        $document_buff .= sprintf('<regdate>%s</regdate>', date('YmdHis', $document_info->reg_date));

        // 첨부파일 정리와 내용 변경을 위한 작업들..
        $content = $document_info->memo;
        $member_srl = $document_info->ismember;

        // use_html옵션에 따른 컨텐츠 정리
        switch($document_info->use_html) {
            case 2 : 
                    // pass
                break;
            default : 
                    $content = nl2br($content);
                break;
        }

        // 그림창고를 이용한 파일 첨부 내용을 찾음 
        $attach_files = array();
        if($member_srl) {
            $match_count = preg_match_all('/\[img:([^\.]*)\.(jpg|gif|png|jpeg)([^\]]*)\]/i', $content, $matches);
            if($match_count) {
                for($i=0;$i<$match_count;$i++) {
                    $image_filename = sprintf('%s.%s', $matches[1][$i], $matches[2][$i]);
                    $attach_files[] = sprintf('%s/icon/member_image_box/%d/%s', $path, $member_srl, $image_filename);
                }
            }

            // content의 내용을 변경
            $content = preg_replace('/\[img:([^\.]*)\.(jpg|gif|png|jpeg),align=([^,]*),width=([^,]*),height=([^,]*),vspace=([^,]*),hspace=([^,]*),border=([^\]]*)\]/i', '<img src="\\1.\\2" align="\\3" width="\\4" height="\\5" border="\\8" alt="\\1.\\2" />', $content);
        }

        if($document_info->file_name1) {
            $attach_files[] = sprintf('%s/%s', $path, $document_info->file_name1);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name1)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name1, $document_info->s_file_name1, $content);
        }
        if($document_info->file_name2) {
            $attach_files[] = sprintf('%s/%s', $path, $document_info->file_name2);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name2)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name2, $document_info->s_file_name2, $content);
        }

        $uploaded_count = count($attach_files);

        // 첨부된 파일 또는 이미지박스를 이용한 파일목록을 구함
        $document_buff .= sprintf('<uploaded_count>%d</uploaded_count>', $uploaded_count);
        $document_buff .= sprintf('<content>%s</content>', addXmlQuote($conetnt));

        // 첨부파일을 읽어서 xml파일에 추가
        $attaches_xml_buff = null;
        for($i=0;$i<$uploaded_count;$i++) {
            $attach_file = $attach_files[$i];
            print $attach_file."<br>";
            if(!file_exists($attach_file)) continue;
            $tmp_arr = explode('/',$attach_file);
            $attach_filename = $tmp_arr[count($tmp_arr)-1];

            $attach_file_buff = getFileContentByBase64Encode($attach_file);
            $attaches_xml_buff .= sprintf('<file name="%s">%s</file>', $attach_filename, $attach_file_buff);
        }
        $document_buff .= sprintf('<files count="%d">%s</files>', $uploaded_count, $attaches_xml_buff);
        exit();

        // 코멘트 목록을 구해옴
        $query = sprintf('select a.*, b.user_id from zetyx_board_comment_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.parent = %d', $id, $document_info->no);
        $comment_result = mysql_query($query) or die(mysql_error());
        $comment_xml_buff = '';
        while($comment_info = mysql_fetch_object($comment_result)) {
            $comment_buff = '';
            $comment_buff .= sprintf('<content>%s</content>', addXmlQuote(nl2br($comment_info->memo)));
            $comment_buff .= sprintf('<password>%s</password>', addXmlQuote($comment_info->password));
            $comment_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($comment_info->user_id));
            if($comment_info->user_id) $comment_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($comment_info->name));
            $comment_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($comment_info->nick_name));
            $comment_buff .= sprintf('<member_srl>%d</member_srl>', $comment_info->ismember);
            $comment_buff .= sprintf('<ipaddress>%s</ipaddress>', addXmlQuote($comment_info->ip));
            $comment_buff .= sprintf('<regdate>%s</regdate>', date('YmdHis', $comment_info->reg_date));
            $comment_xml_buff .= sprintf('<comment>%s</comment>', $comment_buff);
        }
        $document_buff .= sprintf('<comments count="%d">%s</comments>', $document_info->total_comment, $comment_xml_buff);
    
        $xml_buff .= sprintf('<document user_id="%s">%s</document>', addXmlQuote($document_info->user_id), $document_buff);
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
