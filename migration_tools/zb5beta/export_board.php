<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];
    $url = $_POST['url'];
    if(substr($url,-1)!='/') $url .= '/';

    // id를 구함
    $module_srl = ereg_replace('^module\_','',$target_module);

    // 다운로드 헤더 출력
    printDownloadHeader($filename);

    // 게시물의 수를 구함
    $query = sprintf("select count(*) as count from %sarticles where module_srl = '%d'", $db_prefix, $module_srl);
    $count_result = mysql_query($query) or die(mysql_error());
    $count_info = mysql_fetch_object($count_result);
    $total_count = $count_info->count;

    // 게시물을 구함
    $query = sprintf("select * from %sarticles where module_srl = '%d' where listorder < 0 order by listorder", $db_prefix, $module_srl);
    $document_result = mysql_query($query) or die(mysql_error());

    // 헤더 정보 출력
    printf("<root type=\"%s\" module_srl=\"%s\" count=\"%d\">\n", 'module', $module_srl, $total_count);

    $xml_buff = '';
    $sequence = 0;
    while($document_info = mysql_fetch_object($document_result)) {
        $document_buff = null;

        // 기본 정보
        if($document_info->is_notice == 'Y') $document_buff .= sprintf("<is_notice>Y</is_notice>\n");
        $document_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote($document_info->writer));
        $document_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote($document_info->user_id));
        if($document_info->user_id) $document_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote($document_info->writer));
        $document_buff .= sprintf("<password>%s</password>\n", addXmlQuote($document_info->passwd));
        $document_buff .= sprintf("<title>%s</title>\n", addXmlQuote($document_info->title));
        if($document_info->tag) $document_buff .= sprintf("<tag>%s</tag>\n", addXmlQuote($document_info->tag));
        $document_buff .= sprintf("<regdate>%s</regdate>\n", $document_info->regdate);
        $document_buff .= sprintf("<ipaddress>%s</ipaddress>\n", $document_info->ipaddress);
        $document_buff .= sprintf("<comment_count>%d</comment_count>\n", $document_info->comment_cnt);
        $document_buff .= sprintf("<trackback_count>%d</trackback_count>\n", $document_info->trackback_cnt);
        $document_buff .= sprintf("<readed_count>%d</readed_count>\n", $document_info->readed_cnt);
        $document_buff .= sprintf("<voted_count>%d</voted_count>\n", $document_info->voted_cnt);
        $document_buff .= sprintf("<allow_comment>%s</allow_comment>\n", $document_info->allow_comment);
        $document_buff .= sprintf("<allow_trackback>%s</allow_trackback>\n", $document_info->allow_trackback);

        // 첨부파일 정리와 내용 변경을 위한 작업들..
        $content = $document_info->article;
        $article_srl = $document_info->article_srl;
        $document_buff .= sprintf("<content>%s</content>\n", addXmlQuote($content));

        // 첨부파일 목록 가져옴
        if($document_info->file_cnt) {
            $file_query = "select * from {$db_prefix}file where article_srl = '{$article_srl}'";
            $file_result = mysql_query($file_query) or die(mysql_error());
            $attaches_xml_buff = '';
            $uploaded_count = 0;
            while($file_info = mysql_fetch_object($file_result)) {
                if($file_info->is_used != 'Y' || eregi('\.php$',$file_info->s_filename)) continue;
                $attach_filename = sprintf('%s/%s', $path, $file_info->path);
                if(!file_exists($attach_filename)) continue;

                $attaches_xml_buff .= sprintf("<file><filename>%s</filename>\n<url>%s%s</url>\n<download_count>%d</download_count>\n</file>\n", addXmlQuote($file_info->s_filename), addXmlQuote($url), addXmlQuote($file_info->path), $attach_files[$i]['download_count']);

                $uploaded_count ++;
            }
            $document_buff .= sprintf("<uploaded_count>%d</uploaded_count>\n", $uploaded_count);
            $document_buff .= sprintf("<files count=\"%d\">%s</files>\n", $uploaded_count, $attaches_xml_buff);
        }

        // 코멘트 목록을 구해옴
        $query = sprintf("select * from {$db_prefix}comment where article_srl = '{$article_srl}' order by listorder");
        $comment_result = mysql_query($query) or die(mysql_error());
        $comment_xml_buff = '';
        while($comment_info = mysql_fetch_object($comment_result)) {
            $comment_buff = '';
            $comment_buff .= sprintf("<comment_srl>%d</comment_srl>\n", $comment_info->comment_srl);
            $comment_buff .= sprintf("<parent_srl>%d</parent_srl>\n", $comment_info->parent_srl);
            $comment_buff .= sprintf("<content>%s</content>\n", addXmlQuote($comment_info->article));
            $comment_buff .= sprintf("<password>%s</password>\n", addXmlQuote($comment_info->passwd));
            $comment_buff .= sprintf("<user_id>%s</user_id>\n", addXmlQuote($comment_info->user_id));
            if($comment_info->user_id) $comment_buff .= sprintf("<user_name>%s</user_name>\n", addXmlQuote($comment_info->writer));
            $comment_buff .= sprintf("<nick_name>%s</nick_name>\n", addXmlQuote($comment_info->writer));
            $comment_buff .= sprintf("<member_srl>%d</member_srl>\n", $comment_info->member_srl);
            $comment_buff .= sprintf("<ipaddress>%s</ipaddress>\n", addXmlQuote($comment_info->ipaddress));
            $comment_buff .= sprintf("<regdate>%s</regdate>\n", $comment_info->reg_date);
            $comment_xml_buff .= sprintf("<comment>%s</comment>\n", $comment_buff);
        }
        $document_buff .= sprintf("<comments count=\"%d\">%s</comments>\n", $document_info->comment_count, $comment_xml_buff);

        // 트랙백 목록을 구해옴
        $query = sprintf("select * from {$db_prefix}trackback where article_srl = '{$article_srl}' order by listorder");
        $trackback_result = mysql_query($query) or die(mysql_error());
        $trackback_xml_buff = '';
        while($trackback_info = mysql_fetch_object($trackback_result)) {
            $trackback_buff = '';
            $trackback_buff .= sprintf("<url>%s</url>\n", addXmlQuote($trackback_info->url));
            $trackback_buff .= sprintf("<title>%s</title>\n", addXmlQuote($trackback_info->title));
            $trackback_buff .= sprintf("<blog_name>%s</blog_name>\n", addXmlQuote($trackback_info->blog_name));
            $trackback_buff .= sprintf("<excerpt>%s</excerpt>\n", addXmlQuote($trackback_info->excerpt));
            $trackback_buff .= sprintf("<ipaddress>%s</ipaddress>\n", addXmlQuote($trackback_info->ipaddress));
            $trackback_buff .= sprintf("<regdate>%s</regdate>\n", $trackback_info->reg_date);
            $trackback_xml_buff .= sprintf("<trackback>%s</trackback>\n", $trackback_buff);
        }
        $document_buff .= sprintf("<trackbacks count=\"%d\">%s</trackbacks>\n", $document_info->trackback_count, $trackback_xml_buff);
    
        printf("<document sequence=\"%d\">%s</document>\n"."\n", $sequence++, $document_buff);
    }

    print "</root>";
?>
