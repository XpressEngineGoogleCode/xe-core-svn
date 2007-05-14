<?php 
    set_time_limit(0);

    include "lib.php";

    $filename = $_POST['filename'];

    // id를 구함
    $module_srl = ereg_replace('^module\_','',$target_module);

    // 게시물을 구함
    $query = sprintf("select * from {$db_prefix}articles where module_srl = '{$module_srl}' and listorder < 0");
    $document_result = mysql_query($query) or die(mysql_error());

    $xml_buff = '';
    $sequence = 0;
    while($document_info = mysql_fetch_object($document_result)) {
        $document_buff = null;

        // 기본 정보
        if($document_info->is_notice == 'Y') $document_buff .= sprintf('<is_notice>Y</is_notice>');
        $document_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($document_info->writer));
        $document_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($document_info->user_id));
        if($document_info->user_id) $document_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($document_info->writer));
        $document_buff .= sprintf('<password>%s</password>', addXmlQuote($document_info->passwd));
        $document_buff .= sprintf('<title><![CDATA[%s]]></title>', $document_info->title);
        if($document_info->tag) $document_buff .= sprintf('<tag>%s</tag>', addXmlQuote($document_info->tag));
        $document_buff .= sprintf('<regdate>%s</regdate>', $document_info->regdate);
        $document_buff .= sprintf('<ipaddress>%s</ipaddress>', $document_info->ipaddress);
        $document_buff .= sprintf('<comment_count>%d</comment_count>', $document_info->comment_cnt);
        $document_buff .= sprintf('<trackback_count>%d</trackback_count>', $document_info->trackback_cnt);
        $document_buff .= sprintf('<readed_count>%d</readed_count>', $document_info->readed_cnt);
        $document_buff .= sprintf('<voted_count>%d</voted_count>', $document_info->voted_cnt);
        $document_buff .= sprintf('<allow_comment>%d</allow_comment>', $document_info->allow_comment);
        $document_buff .= sprintf('<allow_trackback>%d</allow_trackback>', $document_info->allow_trackback);

        // 첨부파일 정리와 내용 변경을 위한 작업들..
        $content = $document_info->article;
        $article_srl = $document_info->article_srl;

        // 첨부파일 목록 가져옴
        if($document_info->file_cnt) {
            $file_query = "select * from {$db_prefix}file where article_srl = '{$article_srl}'";
            $file_result = mysql_query($file_query) or die(mysql_error());
            $attches_xml_buff = '';
            $uploaded_count = 0;
            while($file_info = mysql_fetch_object($file_result)) {
                if($file_info->is_used != 'Y') continue;
                $attach_filename = sprintf('%s/%s', $path, $file_info->path);
                if(!file_exists($attach_filename)) continue;
                $attches_xml_buff .= sprintf('<file name="%s">', addXmlQuote($file_info->s_filename));
                $attches_xml_buff .= sprintf('<downloaded_count>%d</downloaded_count>', $file_info->download_cnt);
                $attches_xml_buff .= sprintf('<buff><![CDATA[%s]]></buff>', getFileContentByBase64Encode($attach_filename));
                $attches_xml_buff .= '</file>';
                $uploaded_count ++;
            }
            $document_buff .= sprintf('<uploaded_count>%d</uploaded_count>', $uploaded_count);
            $document_buff .= sprintf('<files count="%d">%s</files>', $uploaded_count, $attches_xml_buff);
        }

        // 코멘트 목록을 구해옴
        $query = sprintf("select * from {$db_prefix}comment where article_srl = '{$article_srl}' order by listorder");
        $comment_result = mysql_query($query) or die(mysql_error());
        $comment_xml_buff = '';
        while($comment_info = mysql_fetch_object($comment_result)) {
            $comment_buff = '';
            $comment_buff .= sprintf('<comment_srl>%d</comment_srl>', $comment_info->comment_srl);
            $comment_buff .= sprintf('<parent_srl>%d</parent_srl>', $comment_info->parent_srl);
            $comment_buff .= sprintf('<content><![CDATA[%s]]></content>', nl2br($comment_info->article));
            $comment_buff .= sprintf('<password>%s</password>', addXmlQuote($comment_info->passwd));
            $comment_buff .= sprintf('<user_id>%s</user_id>', addXmlQuote($comment_info->user_id));
            if($comment_info->user_id) $comment_buff .= sprintf('<user_name>%s</user_name>', addXmlQuote($comment_info->writer));
            $comment_buff .= sprintf('<nick_name>%s</nick_name>', addXmlQuote($comment_info->writer));
            $comment_buff .= sprintf('<member_srl>%d</member_srl>', $comment_info->member_srl);
            $comment_buff .= sprintf('<ipaddress>%s</ipaddress>', addXmlQuote($comment_info->ipaddress));
            $comment_buff .= sprintf('<regdate>%s</regdate>', $comment_info->reg_date);
            $comment_xml_buff .= sprintf('<comment>%s</comment>', $comment_buff);
        }
        $document_buff .= sprintf('<comments count="%d">%s</comments>', $document_info->comment_count, $comment_xml_buff);

        // 트랙백 목록을 구해옴
        $query = sprintf("select * from {$db_prefix}trackback where article_srl = '{$article_srl}' order by listorder");
        $trackback_result = mysql_query($query) or die(mysql_error());
        $trackback_xml_buff = '';
        while($trackback_info = mysql_fetch_object($trackback_result)) {
            $trackback_buff = '';
            $trackback_buff .= sprintf('<url>%s</url>', addXmlQuote($trackback_info->url));
            $trackback_buff .= sprintf('<title><![CDATA[%s]]></title>', $trackback_info->title);
            $trackback_buff .= sprintf('<blog_name><![CDATA[%s]]></blog_name>', $trackback_info->blog_name);
            $trackback_buff .= sprintf('<excerpt><![CDATA[%s]]></excerpt>', $trackback_info->excerpt);
            $trackback_buff .= sprintf('<ipaddress>%s</ipaddress>', addXmlQuote($trackback_info->ipaddress));
            $trackback_buff .= sprintf('<regdate>%s</regdate>', $trackback_info->reg_date);
            $trackback_xml_buff .= sprintf('<trackback>%s</trackback>', $trackback_buff);
        }
        $document_buff .= sprintf('<trackbacks count="%d">%s</trackbacks>', $document_info->trackback_count, $trackback_xml_buff);
    
        $xml_buff .= sprintf('<document sequence="%d">%s</document>', $sequence++, $document_buff);
    }

    $xml_buff = sprintf('<root type="zb5beta">%s</root>', $xml_buff);

    // 다운로드
    procDownload($filename, $xml_buff);
?>
