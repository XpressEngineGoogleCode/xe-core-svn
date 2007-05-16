<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // id를 구함
    $id = ereg_replace('^module\_','',$target_module);

    // 게시물을 구함
    $query = sprintf('select a.*, b.user_id from zetyx_board_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.headnum < 0 and a.arrangenum >=0  order by a.headnum, a.arrangenum', $id);
    $document_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    $sequence = 0;
    while($document_info = mysql_fetch_object($document_result)) {
        $document_buff = null;

        // 기본 정보
        if($document_info->headnum <= -2000000000) $document_buff .= sprintf("<is_notice>Y</is_notice>\n");
        if($document_buff->is_secret) $document_buff .= sprintf("<is_secret>Y</is_secret>\n");
        $document_buff .= sprintf("<title>%s</title>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->subject)));
        $document_buff .= sprintf("<readed_count>%d</readed_count>\n", $document_info->hit);
        $document_buff .= sprintf("<voted_count>%d</voted_count>\n", $document_info->vote);
        $document_buff .= sprintf("<comment_count>%d</comment_count>\n", $document_info->total_comment);
        $document_buff .= sprintf("<password>%s</password>\n", addXmlQuote($document_info->password));
        $document_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->user_id)));
        if($document_info->user_id) $document_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->name)));
        $document_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->name)));
        $document_buff .= sprintf("<email_address>%s</email_address>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->email)));
        $document_buff .= sprintf("<homepage>%s</homepage>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$document_info->homepage)));
        $document_buff .= sprintf("<regdate>%s</regdate>\n", date("YmdHis", $document_info->reg_date));
        $document_buff .= sprintf("<ipaddress>%s</ipaddress>\n", $document_info->ip);

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
                    $attach_files[] = array('filename'=>sprintf('%s/icon/member_image_box/%d/%s', $path, $member_srl, $image_filename),"downloaded_count"=>0);
                }
            }

            // content의 내용을 변경
            $content = preg_replace('/\[img:([^\.]*)\.(jpg|gif|png|jpeg),align=([^,]*),width=([^,]*),height=([^,]*),vspace=([^,]*),hspace=([^,]*),border=([^\]]*)\]/i', '<img src="\\1.\\2" align="\\3" width="\\4" height="\\5" border="\\8" alt="\\1.\\2" />', $content);
        }

        if($document_info->file_name1) {
            $attach_files[] = array("filename"=>sprintf('%s/%s', $path, $document_info->file_name1),"downloaded_count"=>$document_info->download1);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name1)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name1, $document_info->s_file_name1, $content);
        }
        if($document_info->file_name2) {
            $attach_files[] = array("filename"=>sprintf('%s/%s', $path, $document_info->file_name2),"downloaded_count"=>$document_info->download2);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name2)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name2, $document_info->s_file_name2, $content);
        }

        $uploaded_count = count($attach_files);

        // 첨부된 파일 또는 이미지박스를 이용한 파일목록을 구함
        $document_buff .= sprintf("<uploaded_count>%d</uploaded_count>\n", $uploaded_count);
        $document_buff .= sprintf("<content>%s</content>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$content)));

        // 첨부파일을 읽어서 xml파일에 추가
        $attaches_xml_buff = null;
        for($i=0;$i<$uploaded_count;$i++) {
            $attach_file = $attach_files[$i]['filename'];
            if(!file_exists($attach_file)) continue;
            $tmp_arr = explode('/',$attach_file);
            $attach_filename = $tmp_arr[count($tmp_arr)-1];

            $attach_file_buff = getFileContentByBase64Encode($attach_file);
            $attaches_xml_buff .= sprintf("<file name=\"%s\">\n<downloaded_count>%d</downloaded_count>\n<buff>\n%s</buff>\n</file>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$attach_filename)), $attach_files[$i]['downloaded_count'], $attach_file_buff);
        }
        $document_buff .= sprintf("<files count=\"%d\">\n%s</files>\n", $uploaded_count, $attaches_xml_buff);

        // 코멘트 목록을 구해옴
        $query = sprintf('select a.*, b.user_id from zetyx_board_comment_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.parent = %d', $id, $document_info->no);
        $comment_result = mysql_query($query) or die(mysql_error());
        $comment_xml_buff = '';
        while($comment_info = mysql_fetch_object($comment_result)) {
            $comment_buff = '';
            $comment_buff .= sprintf("<content>%s</content>\n", addXmlQuote(iconv('EUC-KR','UTF-8',nl2br($comment_info->memo))));
            $comment_buff .= sprintf("<password>%s</password>\n", addXmlQuote($comment_info->password));
            $comment_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$comment_info->user_id)));
            if($comment_info->user_id) $comment_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$comment_info->name)));
            $comment_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote(iconv('EUC-KR','UTF-8',$comment_info->name)));
            $comment_buff .= sprintf("<member_srl>%d</member_srl>\n", $comment_info->ismember);
            $comment_buff .= sprintf("<ipaddress>%s</ipaddress>\n", addXmlQuote($comment_info->ip));
            $comment_buff .= sprintf("<regdate>%s</regdate>\n", date('YmdHis', $comment_info->reg_date));
            $comment_xml_buff .= sprintf("<comment>%s</comment>\n", $comment_buff);
        }
        $document_buff .= sprintf("<comments count=\"%d\">\n%s</comments>\n", $document_info->total_comment, $comment_xml_buff);
    
        $xml_buff .= sprintf("<document sequence=\"%d\">\n%s</document>\n"."\n", $sequence++, base64_encode($document_buff));
    }

    // 다운로드
    procDownload($filename, "<root target=\"module\">\n".$xml_buff."</root>");
?>
