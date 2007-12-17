<?php 
    /**
     * @brief 회원 또는 모듈 데이터 export
     **/

    set_time_limit(0);

    // zMigration class파일 load
    require_once("./classes/zMigration.class.php");

    // library 파일 load
    require_once("lib/lib.php");

    // 입력받은 post 변수를 구함
    $path = $_POST['path'];
    if(substr($path,-1)=='/') $path = substr($path,0,strlen($path)-1);
    $target_module = $_POST['target_module'];
    $module_id = $_POST['module_id'];

    // 입력받은 path를 이용하여 db 정보를 구함
    $db_info = getDBInfo($path);
    if(!$db_info) doError("입력하신 경로가 잘못되었거나 dB 정보를 구할 수 있는 파일이 없습니다");

    // zMigration 객체 생성
    $oMigration = new zMigration($path, $target_module, $module_id, 'UTF-8');
    $oMigration->setDBInfo($db_info);

    // db 접속
    $message = $oMigration->dbConnect();
    if($message) doError($message);
    
    /**
     * 회원 정보 export일 회원 관련 정보를 모두 가져와서 처리
     **/
    if($target_module == 'member') {

        // 전체 대상을 구해서 설정
        $query = sprintf("select count(*) as count from %s_member", $db_info->db_table_prefix);
        $count_result = $oMigration->query($query);
        $count_info = $oMigration->fetch($count_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 회원정보를 구함
        $query = sprintF("select * from %s_member order by member_srl", $db_info->db_table_prefix);
        $member_result = $oMigration->query($query);

        // 추가폼 정보를 구함
        $query = sprintF("select * from %s_member_join_form", $db_info->db_table_prefix);
        $form_result = $oMigration->query($query);
        while($form_item = $oMigration->fetch($form_result)) {
            $form[] = $form_item->column_name;
        }
        

        // 회원정보를 하나씩 돌면서 migration format에 맞춰서 변수화 한후에 printMemberItem 호출
        while($member_info = $oMigration->fetch($member_result)) {
            $obj = null;

            // 일반 변수들
            $obj->user_id = $member_info->user_id;
            $obj->password = $member_info->password;
            $obj->user_name = $member_info->user_name;
            $obj->nick_name = $member_info->nick_name;
            $obj->email = $member_info->email_address;
            $obj->homepage = $member_info->homepage;
            $obj->blog = $member_info->blog;
            $obj->birthday = $member_info->birthday;
            $obj->allow_mailing = $member_info->mailing;
            $obj->regdate = $member_info->regdate;

            // 이미지네임
            $image_name_file = sprintf('%s/files/member_extra_info/image_name/%s%d.gif', $path, getNumberingPath($member_info->member_srl), $member_info->member_srl);
            if(file_exists($image_name_file)) $obj->image_nickname = $image_name_file;

            // 이미지마크
            $image_mark_file = sprintf('%s/files/member_extra_info/image_mark/%s%d.gif', $path, getNumberingPath($member_info->member_srl), $member_info->member_srl);
            if(file_exists($image_mark_file)) $obj->image_mark = $image_mark_file;

            // 프로필 이미지
            $image_profile_file = sprintf('%s/files/member_extra_info/profile_image/%s%d.gif', $path, getNumberingPath($member_info->member_srl), $member_info->member_srl);
            if(file_exists($image_profile_file)) $obj->profile_image = $image_profile_file;

            // 서명
            $sign_filename = sprintf('%s/files/member_extra_info/signature/%s%d.signature.php', $path, getNumberingPath($member_info->member_srl), $member_info->member_srl);
            if(file_exists($sign_filename)) {
                $f = fopen($sign_filename, "r");
                $signature = trim(fread($f, filesize($sign_filename)));
                fclose($f);

                $obj->signature = $signature;
            }

            // 확장변수 칸에 입력된 변수들은 제로보드XE의 멤버 확장변수를 통해서 사용될 수 있음
            unset($extra_vars);
            $extra_vars = unserialize($member_info->extra_vars);
            if($form && $extra_vars) {
                foreach($form as $f) if($extra_vars->{$f}) $obj->extra_vars[$f] = $extra_vars->{$f};
            }

            $oMigration->printMemberItem($obj);
        }

        // 헤더 정보를 출력
        $oMigration->printFooter();

    /**
     * 게시판 정보 export일 경우
     **/
    } else {
        // module_srl 변수를 세팅
        $module_srl = $module_id;

        // 모듈 정보를 구함
        $query = sprintf("select * from %s_modules where module_srl = '%s'", $db_info->db_table_prefix, $module_srl);
        $module_info_result = $oMigration->query($query);
        $module_info = $oMigration->fetch($module_info_result);
        $module_title = $module_info->browser_title;
        $oMigration->setFilename($module_title.'.xml');
        
        // 게시물의 수를 구함
        $query = sprintf("select count(*) as count from %s_documents where module_srl = '%d'", $db_info->db_table_prefix, $module_srl);
        $count_result = $oMigration->query($query);
        $count_info = $oMigration->fetch($count_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 게시글은 역순(오래된 순서)으로 구함
        $query = sprintf("select * from %s_documents where module_srl = '%d' order by document_srl", $db_info->db_table_prefix, $module_srl);
        $document_result = $oMigration->query($query);

        while($document_info = $oMigration->fetch($document_result)) {
            $obj = null;

            $obj->title = $document_info->title;
            $obj->content = $document_info->content;
            $obj->readed_count = $document_info->readed_count;
            $obj->voted_count = $document_info->voted_count;
            $obj->user_id = $document_info->user_id;
            $obj->nick_name = $document_info->nick_name;
            $obj->email = $document_info->email_address;
            $obj->homepage = $document_info->homepage;
            $obj->password = $document_info->password;
            $obj->ipaddress = $document_info->ipaddress;
            $obj->allow_comment = $document_info->allow_comment;
            $obj->lock_comment = $document_info->lock_comment;
            $obj->allow_trackback = $document_info->allow_trackback;
            $obj->is_secret = $document_info->is_secret;
            $obj->regdate =  $document_info->regdate;
            $obj->update = $document_info->last_update;
            $obj->tags = $document_info->tags;

            // 게시글의 엮인글을 구함 
            $query = sprintf("select * from %s_trackbacks where document_srl = '%s' order by trackback_srl", $db_info->db_table_prefix, $document_info->document_srl);

            $trackbacks = array();
            $trackback_result = $oMigration->query($query);
            while($trackback_info = $oMigration->fetch($trackback_result)) {
                $trackback_obj = null;
                $trackback_obj->url = $trackback_info->url;
                $trackback_obj->title = $trackback_info->title;
                $trackback_obj->blog_name = $trackback_info->blog_name;
                $trackback_obj->excerpt = $trackback_info->excerpt;
                $trackback_obj->regdate = $trackback_info->regdate;
                $trackback_obj->ipaddress = $trackback_info->ipaddress;
                $trackbacks[] = $trackback_obj;
            }
            $obj->trackbacks = $trackbacks;

            // 게시글의 댓글을 구함
            $comments = array();
            $query = sprintf("select * from %s_comments where document_srl = '%d' order by comment_srl", $db_info->db_table_prefix, $document_info->document_srl);
            $comment_result = $oMigration->query($query);
            while($comment_info = $oMigration->fetch($comment_result)) {
                $comment_obj = null;

                // 현재 사용중인 primary key값을 sequence로 넣어두면 parent와 결합하여 depth를 이루어서 importing함
                $comment_obj->sequence = $comment_info->comment_srl;
                $comment_obj->parent = $comment_info->parent_srl; 

                $comment_obj->is_secret = $comment_info->is_secret;
                $comment_obj->content = $comment_info->content;
                $comment_obj->voted_count = $comment_info->voted_count;
                $comment_obj->notify_message = $comment_info->notify_message;
                $comment_obj->password = $comment_info->password;
                $comment_obj->user_id = $comment_info->user_id;
                $comment_obj->nick_name = $comment_info->nick_name;
                $comment_obj->email = $comment_info->email_address;
                $comment_obj->homepage = $comment_info->homepage;
                $comment_obj->regdate = $comment_info->regdate;
                $comment_obj->update = $comment_info->last_update;
                $comment_obj->ipaddress = $comment_info->ipaddress;

                // 댓글의 첨부파일 체크
                $files = array();

                $file_query = sprintf("select * from %s_files where upload_target_srl = '%d'", $db_info->db_table_prefix, $comment_info->comment_srl);
                $file_result = $oMigration->query($file_query);
                while($file_info = $oMigration->fetch($file_result)) {
                    $filename = $file_info->source_filename;
                    $download_count = $file_info->download_count;
                    $file = sprintf("%s/%s", $path, $file_info->uploaded_filename);

                    $file_obj = null;
                    $file_obj->filename = $filename;
                    $file_obj->file = $file;
                    $file_obj->download_count = $download_count;
                    $files[] = $file_obj;

                    // 이미지등의 파일일 경우 직접 링크를 수정
                    if($file_info->direct_download == 'Y') {
                        preg_match_all('/("|\')([^"^\']*?)('.preg_quote(urlencode($filename)).')("|\')/i',$comment_obj->content,$matches);
                        $mat = $matches[0];
                        if(count($mat)) {
                            foreach($mat as $m) {
                                $comment_obj->content = str_replace($m, '"'.$filename.'"', $comment_obj->content);
                            }
                        }
                    // binary 파일일 경우 역시 링클르 변경
                    } else {
                        preg_match_all('/("|\')([^"^\']*?)('.preg_quote(urlencode($file_info->sid)).')("|\')/i',$comment_obj->content,$matches);
                        $mat = $matches[0];
                        if(count($mat)) {
                            foreach($mat as $m) {
                                $comment_obj->content = str_replace($m, '"'.$filename.'"', $comment_obj->content);
                            }
                        }
                    }
                }
                if(count($files)) $comment_obj->attaches = $files;

                $comments[] = $comment_obj;
            }

            $obj->comments = $comments;


            // 첨부파일 구함
            $files = array();

            $file_query = sprintf("select * from %s_files where upload_target_srl = '%d'", $db_info->db_table_prefix, $document_info->document_srl);
            $file_result = $oMigration->query($file_query);

            while($file_info = $oMigration->fetch($file_result)) {
                $filename = $file_info->source_filename;
                $download_count = $file_info->download_count;
                $file = sprintf("%s/%s", $path, $file_info->uploaded_filename);

                $file_obj = null;
                $file_obj->filename = $filename;
                $file_obj->file = $file;
                $file_obj->download_count = $download_count;
                $files[] = $file_obj;

                // 이미지등의 파일일 경우 직접 링크를 수정
                if($file_info->direct_download == 'Y') {
                    preg_match_all('/("|\')([^"^\']*?)('.preg_quote(urlencode($filename)).')("|\')/i',$obj->content,$matches);
                    $mat = $matches[0];
                    if(count($mat)) {
                        foreach($mat as $m) {
                            $obj->content = str_replace($m, '"'.$filename.'"', $obj->content);
                        }
                    }
                // binary 파일일 경우 역시 링클르 변경
                } else {
                    preg_match_all('/("|\')([^"^\']*?)('.preg_quote(urlencode($file_info->sid)).')("|\')/i',$obj->content,$matches);
                    $mat = $matches[0];
                    if(count($mat)) {
                        foreach($mat as $m) {
                            $obj->content = str_replace($m, '"'.$filename.'"', $obj->content);
                        }
                    }
                }
            }
            $obj->attaches = $files;

            $oMigration->printPostItem($document_info->document_srl, $obj);
        }

        // 헤더 정보를 출력
        $oMigration->printFooter();
    }
?>
