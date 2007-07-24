<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];
    $url = $_POST['url'];
    if(substr($url,-1)!='/') $url .= '/';

    // id를 구함
    $id = ereg_replace('^module\_','',$target_module);

    // 게시판 정보를 구함
    $query = "select * from zetyx_admin_table where name='".$id."'";
    $result = mysql_query($query) or die(mysql_error());
    $module_info = mysql_fetch_object($result);

    // 카테고리를 사용시에 카테고리 정보를 구함
    if($module_info->use_category) {
        $query = "select * from zetyx_board_category_".$id;
        $result = mysql_query($query) or die(mysql_error());
        while($tmp = mysql_fetch_object($result)) {
            $category_list[$tmp->no] = $tmp->name;
        }
    }

    // 다운로드 헤더 출력
    printDownloadHeader($filename);

    // 게시물의 수를 구함
    $query = sprintf("select count(*) as count from zetyx_board_%s", $id);
    $count_result = mysql_query($query) or die(mysql_error());
    $count_info = mysql_fetch_object($count_result);
    $total_count = $count_info->count;

    // 게시물을 구함
    $query = sprintf('select a.*, b.user_id from zetyx_board_%s a left outer join zetyx_member_table b on a.ismember = b.no order by a.headnum desc, a.arrangenum desc', $id);
    $document_result = mysql_query($query) or die(mysql_error());

    // 헤더 정보 출력
    printf("<root type=\"%s\" id=\"%s\" count=\"%d\">", 'module', $id, $total_count);

    // 카테고리를 사용중이면 카테고리 정보 출력
    if($module_info->use_category && count($category_list)) {
        print("<categories>\n");
        foreach($category_list as $key => $val) {
            printf("<category>%s</category>", addXmlQuote($val) );
        }
        print("</categories>\n");
    }

    $xml_buff = '';
    $sequence = 0;
    while($document_info = mysql_fetch_object($document_result)) {
        $document_buff = null;

        // 기본 정보
        if($document_info->headnum <= -2000000000) $document_buff .= sprintf("<is_notice>Y</is_notice>\n");
        if($document_info->is_secret) $document_buff .= sprintf("<is_secret>Y</is_secret>\n");

        if($module_info->use_category && $document_info->category && $category_list[$document_info->category]) {
            $document_buff .= sprintf("<category>%s</category>\n", addXmlQuote($category_list[$document_info->category]));
        }
        $document_buff .= sprintf("<title>%s</title>\n", addXmlQuote($document_info->subject));
        $document_buff .= sprintf("<readed_count>%d</readed_count>\n", $document_info->hit);
        $document_buff .= sprintf("<voted_count>%d</voted_count>\n", $document_info->vote);
        $document_buff .= sprintf("<comment_count>%d</comment_count>\n", $document_info->total_comment);
        $document_buff .= sprintf("<password>%s</password>\n", addXmlQuote($document_info->password));
        $document_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote($document_info->user_id));
        if($document_info->user_id) $document_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote($document_info->name));
        $document_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote($document_info->name));
        $document_buff .= sprintf("<email_address>%s</email_address>\n", addXmlQuote($document_info->email));
        $document_buff .= sprintf("<homepage>%s</homepage>\n", addXmlQuote($document_info->homepage));
        $document_buff .= sprintf("<regdate>%s</regdate>\n", date("YmdHis", $document_info->reg_date));
        $document_buff .= sprintf("<ipaddress>%s</ipaddress>\n", $document_info->ip);
        $document_buff .= sprintf("<allow_comment>%s</allow_comment>\n", 'Y');
        $document_buff .= sprintf("<lock_comment>%s</lock_comment>\n", 'N');
        $document_buff .= sprintf("<allow_trackback>%s</allow_trackback>\n", 'Y');

        // 첨부파일 정리와 내용 변경을 위한 작업들..
        $content = stripslashes($document_info->memo);

        if($document_info->sitelink1) $content = sprintf('<a href="%s">%s</a>%s<br />', $document_info->sitelink1, $document_info->sitelink1, $content);
        if($document_info->sitelink2) $content = sprintf('<a href="%s">%s</a>%s<br />', $document_info->sitelink2, $document_info->sitelink2, $content);
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
                    $attach_files[] = array('filename'=>sprintf('icon/member_image_box/%d/%s', $member_srl, $image_filename),"download_count"=>0);
                }
            }

            // content의 내용을 변경
            $content = preg_replace('/\[img:([^\.]*)\.(jpg|gif|png|jpeg),align=([^,]*),width=([^,]*),height=([^,]*),vspace=([^,]*),hspace=([^,]*),border=([^\]]*)\]/i', '<img src="\\1.\\2" align="\\3" width="\\4" height="\\5" border="\\8" alt="\\1.\\2" />', $content);
        }

        if($document_info->file_name1) {
            $attach_files[] = array("filename"=>$document_info->file_name1,"download_count"=>$document_info->download1);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name1)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name1, $document_info->s_file_name1, $content);
        }
        if($document_info->file_name2) {
            $attach_files[] = array("filename"=>$document_info->file_name2,"download_count"=>$document_info->download2);
            if(eregi('(jpg|gif|jpeg|png)$', $document_info->file_name2)) $content = sprintf('<img src="%s" border="0" alt="%s" /><br />%s', $document_info->s_file_name2, $document_info->s_file_name2, $content);
        }

        $uploaded_count = count($attach_files);

        // 첨부된 파일 또는 이미지박스를 이용한 파일목록을 구함
        $document_buff .= sprintf("<uploaded_count>%d</uploaded_count>\n", $uploaded_count);
        $document_buff .= sprintf("<content>%s</content>\n", addXmlQuote($content));

        // 첨부파일을 읽어서 xml파일에 추가
        $attaches_xml_buff = null;
        for($i=0;$i<$uploaded_count;$i++) {
            $attach_file = $attach_files[$i]['filename'];
            if(!file_exists($path.'/'.$attach_file)) continue;
            $tmp_arr = explode('/',$attach_file);
            $attach_filename = $tmp_arr[count($tmp_arr)-1];

            $attaches_xml_buff .= sprintf("<file><filename>%s</filename>\n<url>%s%s</url>\n<download_count>%d</download_count>\n</file>\n", addXmlQuote($attach_filename), addXmlQuote($url), addXmlQuote($attach_file), $attach_files[$i]['download_count']);
        }
        $document_buff .= sprintf("<files count=\"%d\">\n%s</files>\n", $uploaded_count, $attaches_xml_buff);

        // 코멘트 목록을 구해옴
        $query = sprintf('select a.*, b.user_id from zetyx_board_comment_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.parent = %d', $id, $document_info->no);
        $comment_result = mysql_query($query) or die(mysql_error());
        $comment_xml_buff = '';
        while($comment_info = mysql_fetch_object($comment_result)) {
            $comment_buff = '';
            $comment_buff .= sprintf("<content>%s</content>\n", addXmlQuote(nl2br($comment_info->memo)));
            $comment_buff .= sprintf("<password>%s</password>\n", addXmlQuote($comment_info->password));
            $comment_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote($comment_info->user_id));
            if($comment_info->user_id) $comment_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote($comment_info->name));
            $comment_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote($comment_info->name));
            $comment_buff .= sprintf("<member_srl>%d</member_srl>\n", $comment_info->ismember);
            $comment_buff .= sprintf("<ipaddress>%s</ipaddress>\n", addXmlQuote($comment_info->ip));
            $comment_buff .= sprintf("<regdate>%s</regdate>\n", date('YmdHis', $comment_info->reg_date));
            $comment_xml_buff .= sprintf("<comment>%s</comment>\n", $comment_buff);
        }
        $document_buff .= sprintf("<comments count=\"%d\">\n%s</comments>\n", $document_info->total_comment, $comment_xml_buff);
    
        printf("<document sequence=\"%d\">\n%s</document>\n"."\n", $sequence++, $document_buff);
    }

    print '</root>';
?>
