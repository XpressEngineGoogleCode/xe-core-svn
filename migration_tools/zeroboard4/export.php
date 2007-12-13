<?php 
    /**
     * @brief xml 데이터를export하는 파일
     **/

    // 일단 많은 수의 회원이 있을 수 있기에 time limit을 0으로 세팅
    set_time_limit(0);

    // zMigration class파일 load
    require_once("./classes/zMigration.class.php");

    // library 파일 load
    require_once("lib/lib.php");

    // 입력받은 post 변수를 구함
    $charset = $_POST['charset'];
    $path = $_POST['path'];
    if(substr($path,-1)=='/') $path = substr($path,0,strlen($path)-1);
    $target_module = $_POST['target_module'];
    $module_id = $_POST['module_id'];

    //$charset = "EUC-KR";
    //$path = "../bbs";
    //$target_module = "module";
    //$module_id = "movie";

    // 입력받은 path를 이용하여 db 정보를 구함
    $db_info = getDBInfo($path);
    if(!$db_info) doError("입력하신 경로가 잘못되었거나 dB 정보를 구할 수 있는 파일이 없습니다");

    // zMigration 객체 생성
    $oMigration = new zMigration($path, $target_module, $module_id, $charset);

    // db 접속
    $message = $oMigration->dbConnect($db_info);
    if($message) doError($message);

    /**
     * 회원 정보 export일 회원 관련 정보를 모두 가져와서 처리
     **/
    if($target_module == 'member') {

        // 이미지닉네임, 이미지마크 경로, 프로필 이미지, 서명 구함
        $image_nickname_path = sprintf('%s/icon/private_name/',$path);
        $image_mark_path = sprintf('%s/icon/private_icon/',$path);

        // 전체 대상을 구해서 설정
        $query = "select count(*) as count from zetyx_member_table";
        $count_result = $oMigration->query($query);
        $count_info = mysql_fetch_object($count_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 회원정보를 역순(오래된 순)으로 구해옴
        $query = "select * from zetyx_member_table order by no asc";
        $member_result = $oMigration->query($query) or die(mysql_error());

        // 회원정보를 하나씩 돌면서 migration format에 맞춰서 변수화 한후에 printMemberItem 호출
        while($member_info = mysql_fetch_object($member_result)) {
            $obj = null;

            // 일반 변수들
            $obj->user_id = $member_info->user_id;
            $obj->password = $member_info->password;
            $obj->user_name = $member_info->name;
            $obj->nick_name = $member_info->name;
            $obj->email = $member_info->email;
            $obj->homepage = $member_info->homepage;
            $obj->blog = $member_info->blog;
            $obj->birthday = date("YmdHis", $member_info->birth);
            $obj->allow_mailing = $member_info->user_id!=0?'Y':'N';
            $obj->point = $member_info->point1+$member_info->point2;
            $obj->signature = '';

            // 이미지이름, 이미지마크, 프로필이미지등은 경로를 입력
            $obj->image_nickname = sprintf("%s%d.gif", $image_nickname_path, $member_info->no);
            $obj->image_mark = sprintf("%s%d.gif", $image_mark_path, $member_info->no);
            $obj->profile_image = '';

            // 확장변수 칸에 입력된 변수들은 제로보드XE의 멤버 확장변수를 통해서 사용될 수 있음
            $obj->extra_vars = array(
                'icq' => $member_info->icq,
                'aol' => $member_info->aol,
                'msn' => $member_info->msn,
                'job' => $member_info->job,
                'hobby' => $member_info->hobby,
                'home_address' => $member_info->home_address,
                'home_tel' => $member_info->home_tel,
                'office_address' => $member_info->office_address,
                'office_tel' => $member_info->office_tel,
                'handphone' => $member_info->handphone,
            );

            $oMigration->printMemberItem($obj);
        }

        // 헤더 정보를 출력
        $oMigration->printFooter();

    /**
     * 게시판 정보 export일 경우
     **/
    } else {

        // 그림창고, 첨부파일 디렉토리 경로를 미리 구함
        $image_box_path = sprintf('%s/icon/member_image_box',$path);

        // 게시판 정보를 구함
        $query = "select * from zetyx_admin_table where name='".$module_id."'";
        $module_info_result = $oMigration->query($query);
        $module_info = mysql_fetch_object($module_info_result);

        // 게시물의 수를 구함
        $query = sprintf("select count(*) as count from zetyx_board_%s", $module_id);
        $count_info_result = $oMigration->query($query);
        $count_info = mysql_fetch_object($count_info_result);
        $oMigration->setItemCount($count_info->count);

        // 헤더 정보를 출력
        $oMigration->printHeader();

        // 카테고리를 구함
        if($module_info->use_category) {
            $query = "select * from zetyx_board_category_".$module_id;
            $category_result = $oMigration->query($query);
            while($category_info= mysql_fetch_object($category_result)) {
                $category_list[$category_info->no] = strip_tags($category_info->name);
            }
        }

        // 카테고리 정보 출력
        $oMigration->printCategoryItem($category_list);

        // 게시글은 역순(오래된 순서)으로 구함
        $query = sprintf('select a.*, b.user_id from zetyx_board_%s a left outer join zetyx_member_table b on a.ismember = b.no order by a.headnum desc, a.arrangenum desc', $module_id);
        $document_result = $oMigration->query($query);

        while($document_info = mysql_fetch_object($document_result)) {
            $obj = null;

            if($module_info->use_category && $document_info->category) $obj->category = $category_list[$document_info->category];
            $obj->title = $document_info->subject;
            $obj->content = $document_info->memo;
            $obj->readed_count = $document_info->hit;
            $obj->voted_count = $document_info->vote;
            $obj->user_id = $document_info->user_id;
            $obj->nick_name = $document_info->name;
            $obj->email = $document_info->email;
            $obj->homepage = $document_info->homepage;
            $obj->password = $document_info->password;
            $obj->ipaddress = $document_info->ip;
            $obj->allow_comment = 'Y';
            $obj->lock_comment = 'N';
            $obj->allow_trackback = 'Y';
            $obj->is_secret = trim($document_info->is_secret)?'Y':'N';
            $obj->regdate =  date("YmdHis", $document_info->reg_date);
            $obj->update = null;

            // use_html옵션에 따른 컨텐츠 정리
            if($document_info->use_html != 2) $obj->content = nl2br($obj->content);

            // 제로보드4의 sitelink1, 2가 있을 경우 본문 상단에 추가
            if($document_info->sitelink1) $obj->content = sprintf('<a href="%s" onclick="window.open(this.href);return false;">%s</a>%s<br />', $document_info->sitelink1, $document_info->sitelink1, $obj->content);
            if($document_info->sitelink2) $obj->content = sprintf('<a href="%s" onclick="window.open(this.href);return false;">%s</a>%s<br />', $document_info->sitelink2, $document_info->sitelink2, $obj->content);

            // 게시판의 기타 정보를 구함 (다른 기타 정보가 있을 경우 추가하면 됨 (20개까지 가능)
            if($document_info->x) $obj->extra_vars[] = $document_info->x;
            if($document_info->y) $obj->extra_vars[] = $document_info->y;

            // 게시글의 엮인글을 구함 (제로보드4는 엮인글이 없어서 기본으로 데이터를 입력하지 않음)
            /*
            $trackbacks = array();
            $query = sprintf("select * from 테이블 where article_srl = '{$article_srl}' order by listorder");
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
            */

            // 게시글의 댓글을 구함
            $comments = array();
            $query = sprintf('select a.*, b.user_id from zetyx_board_comment_%s a left outer join zetyx_member_table b on a.ismember = b.no where a.parent = %d order by no asc', $module_id, $document_info->no);
            $comment_result = $oMigration->query($query);
            while($comment_info = mysql_fetch_object($comment_result)) {
                $comment_obj = null;
                // 현재 사용중인 primary key값을 sequence로 넣어두면 parent와 결합하여 depth를 이루어서 importing함
                $comment_obj->sequence = $comment_obj->no;

                // 제로보드4는 댓글에 depth가 없어서 parent를 0으로 세팅. 다른 프로그램이라면 부모 고유값을 입력해주면 됨
                $comment_obj->parent = 0; 

                $comment_obj->is_secret = 'N';
                $comment_obj->content = nl2br($comment_info->memo);
                $comment_obj->voted_count = 0;
                $comment_obj->notify_message = 'N';
                $comment_obj->password = $comment_info->password;
                $comment_obj->user_id = $comment_obj->user_id;
                $comment_obj->nick_name = $comment_obj->name;
                $comment_obj->email = $comment_obj->email;
                $comment_obj->homepage = $comment_obj->homepage;
                $comment_obj->update = $comment_obj->update;
                $comment_obj->regdate = date('YmdHis', $comment_info->reg_date);
                $comment_obj->ipaddress = $comment_obj->ip;

                // 댓글의 첨부파일을 구함 (제로보드4에는 없기에 pass~)
                $files = array();
                $comment_obj->attaches = $files;

                $comments[] = $comment_obj;

            }
            $obj->comments = $comments;


            // 첨부파일 구함 (제로보드4의 경우 이미지박스 + 첨부파일1,2(..more) 를 관리
            $files = array();

            // 그림창고 정리 
            $member_srl = $document_info->ismember;
            if($member_srl) {

                $match_count = preg_match_all('/\[img:([^\.]*)\.(jpg|gif|png|jpeg)([^\]]*)\]/i', $obj->content, $matches);
                if($match_count) {
                    for($i=0;$i<$match_count;$i++) {
                        $image_filename = sprintf('%s.%s', $matches[1][$i], $matches[2][$i]);
                        $file_obj = null;
                        $file_obj->filename = $image_filename;
                        $file_obj->file = sprintf('%s/%d/%s', $image_box_path, $member_srl, $image_filename);
                        $file_obj->download_count = 0;
                        $files[] = $file_obj;
                    }
                }

                // content의 내용을 변경 (이미지 경로를 파일이름만으로 해 놓으면 차후 import시에 경로를 입력하도록 변경함)
                $obj->content = preg_replace('/\[img:([^\.]*)\.(jpg|gif|png|jpeg),align=([^,]*),width=([^,]*),height=([^,]*),vspace=([^,]*),hspace=([^,]*),border=([^\]]*)\]/i', '<img src="\\1.\\2" align="\\3" width="\\4" height="\\5" border="\\8" alt="\\1.\\2" />', $obj->content);
            }

            // 첨부파일 처리 (기본 2개인데 일단 20개로 만들어 보았음)
            for($i=1;$i<=20;$i++) {
                $file_name = $document_info->{"file_name".$i};
                if(!$file_name) continue;

                $filename = $document_info->{"s_file_name".$i};
                $download_count = $download_count->{"download".$i};
                $file = sprintf("%s/%s", $path, $file_name);

                $file_obj = null;
                $file_obj->filename = $filename;
                $file_obj->file = $file;
                $file_obj->download_count = $download_count;
                $files[] = $file_obj;

                // 이미지 파일이라면 내용 상단에 이미지 추가
                if(eregi('\.(jpg|gif|jpeg|png)$', $file_name1)) $obj->content = sprintf('<img src="%s" border="0" alt="" /><br />%s', $filename,  $obj->content);
            }

            $obj->attaches = $files;

            $oMigration->printPostItem($document_info->no, $obj);
        }

        // 헤더 정보를 출력
        $oMigration->printFooter();
    }
?>
