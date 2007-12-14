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

    // db 접속
    $message = $oMigration->dbConnect($db_info);
    if($message) doError($message);

    $oMigration->query("set names 'utf8'");
    
    /**
     * 회원 정보 export일 회원 관련 정보를 모두 가져와서 처리
     **/
    if($target_module == 'member') {

        // 전체 대상을 구해서 설정
        $query = sprintf("select count(*) as count from %smember_list", $db_info->db_prefix);
        $count_result = $oMigration->query($query);
        $count_info = mysql_fetch_object($count_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 회원정보를 구함
        $query = sprintF("select * from %smember_list order by member_srl", $db_info->db_prefix);
        $member_result = $oMigration->query($query) or die(mysql_error());

        // 추가폼 정보를 구함
        $query = sprintF("select * from %smember_signup_manager", $db_info->db_prefix);
        $form_result = $oMigration->query($query) or die(mysql_error());
        while($form_item = mysql_fetch_object($form_result)) {
            if(in_array($form_item->name, array('homepage','blog'))) continue;
            $form[] = $form_item->name;
        }
        

        // 회원정보를 하나씩 돌면서 migration format에 맞춰서 변수화 한후에 printMemberItem 호출
        while($member_info = mysql_fetch_object($member_result)) {
            $obj = null;

            // 일반 변수들
            $obj->user_id = $member_info->user_id;
            $obj->password = $member_info->passwd;
            $obj->user_name = $member_info->user_name;
            $obj->nick_name = $member_info->nick_name;
            $obj->email = $member_info->email_address;
            $obj->homepage = $member_info->homepage;
            $obj->blog = $member_info->blog;
            $obj->birthday = date("YmdHis", $member_info->birth);
            $obj->allow_mailing = $member_info->mailing;
            $obj->point = $member_info->point;
            $obj->signature = $member_info->sign;

            // 이미지네임
            if($member_info->image_nick) {
                $pos = strpos($member_info->image_nick, 'files');
                $image_nickname_file = sprintf('%s/%s', $path, substr($member_info->image_nick, $pos, strlen($member_info->image_nick)));
                $obj->image_nickname = $image_nickname_file;
            }

            // 이미지마크
            if($member_info->image_mark) {
                $pos = strpos($member_info->image_mark, 'files');
                $image_mark_file = sprintf('%s/%s', $path, substr($member_info->image_mark, $pos, strlen($member_info->image_mark)));
                $obj->image_mark = $image_mark_file;
            }

            // 프로필 이미지
            if($member_info->profile_image) {
                $pos = strpos($member_info->profile_image, 'files');
                $profile_image_file = sprintf('%s/%s', $path, substr($member_info->profile_image, $pos, strlen($member_info->profile_image)));
                $obj->profile_image = $profile_image;
            }

            // 확장변수 칸에 입력된 변수들은 제로보드XE의 멤버 확장변수를 통해서 사용될 수 있음
            unset($extra_vars);
            $extra_vars = unserialize( base64_decode( $member_info->extend_val) );
            if($extra_vars->homepage) $obj->homepage = $extra_vars->homepage;
            if($extra_vars->blog) $obj->blog = $extra_vars->blog;

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
        $query = sprintf("select * from %smodule_manager where module_srl = '%s'", $db_info->db_prefix, $module_srl);
        $module_info_result = $oMigration->query($query);
        $module_info = mysql_fetch_object($module_info_result);
        $module_title = $module_info->title;
        $oMigration->setFilename($module_title.'.xml');
        
        // 게시물의 수를 구함
        $query = sprintf("select count(*) as count from %sarticles where module_srl = '%d'", $db_info->db_prefix, $module_srl);
        $count_result = $oMigration->query($query) or die(mysql_error());
        $count_info = mysql_fetch_object($count_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 게시글은 역순(오래된 순서)으로 구함
        $query = sprintf("select * from %sarticles where module_srl = '%d' order by article_srl", $db_info->db_prefix, $module_srl);
        $document_result = $oMigration->query($query) or die(mysql_error());

        while($document_info = mysql_fetch_object($document_result)) {
            $obj = null;

            $obj->title = $document_info->title;
            $obj->content = $document_info->article;
            $obj->readed_count = $document_info->readed_cnt;
            $obj->voted_count = $document_info->voted_cnt;
            $obj->user_id = $document_info->user_id;
            $obj->nick_name = $document_info->writer;
            $obj->password = $document_info->passwd;
            $obj->ipaddress = $document_info->ipaddress;
            $obj->allow_comment = $document_info->allow_comment;
            $obj->lock_comment = 'N';
            $obj->allow_trackback = $document_info->allow_trackback;
            $obj->is_secret = 'N';
            $obj->regdate =  $document_info->reg_date;
            $obj->update = null;
            $obj->tags = $document_info->tag;

            // 게시글의 엮인글을 구함 
            $query = sprintf("select * from %strackback where article_srl = '%s' order by listorder", $db_info->db_prefix, $document_info->article_srl);
            $trackbacks = array();
            $trackback_result = $oMigration->query($query);
            while($trackback_info = mysql_fetch_object($trackback_result)) {
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
            $query = sprintf("select * from %scomment where article_srl = '%d' order by listorder", $db_info->db_prefix, $document_info->article_srl);
            $comment_result = $oMigration->query($query);
            while($comment_info = mysql_fetch_object($comment_result)) {
                $comment_obj = null;

                // 현재 사용중인 primary key값을 sequence로 넣어두면 parent와 결합하여 depth를 이루어서 importing함
                $comment_obj->sequence = $comment_info->comment_srl;
                $comment_obj->parent = $comment_info->parent_srl; 

                $comment_obj->is_secret = 'N';
                $comment_obj->content = $comment_info->article;
                $comment_obj->voted_count = 0;
                $comment_obj->notify_message = 'N';
                $comment_obj->password = $comment_info->passwd;
                $comment_obj->user_id = $comment_info->user_id;
                $comment_obj->nick_name = $comment_info->writer;
                $comment_obj->regdate = $comment_info->reg_date;
                $comment_obj->ipaddress = $comment_info->ipaddress;

                $comments[] = $comment_obj;
            }

            $obj->comments = $comments;


            // 첨부파일 구함 (제로보드4의 경우 이미지박스 + 첨부파일1,2(..more) 를 관리
            $files = array();

            $file_query = sprintf("select * from %sfile where article_srl = '%d'", $db_info->db_prefix, $document_info->article_srl);
            $file_result = $oMigration->query($file_query) or die(mysql_error());
            while($file_info = mysql_fetch_object($file_result)) {
                $filename = $file_info->s_filename;
                $download_count = $file_info->download_cnt;
                $file = sprintf("%s/%s", $path, $file_info->path);

                $file_obj = null;
                $file_obj->filename = $filename;
                $file_obj->file = $file;
                $file_obj->download_count = $download_count;
                $files[] = $file_obj;

                // 이미지 파일일 경우 내용 변경
                $obj->content = preg_replace('/http:\/\/([^\?]*)\?filename='.$file_info->filename.'/is', $filename, $obj->content);
            }

            $obj->attaches = $files;

            $oMigration->printPostItem($document_info->article_srl, $obj);
        }

        // 헤더 정보를 출력
        $oMigration->printFooter();
    }
?>
