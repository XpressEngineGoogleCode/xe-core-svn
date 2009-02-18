<?php
    /**
     * @class content
     * @author sol (sol@ngleader.com)
     * @brief content를 출력하는 위젯
     * @version 0.1
     **/

    class content extends WidgetHandler {

        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/

        function proc($args) {


        // 기본값으로 set

            // 정렬 대상
            if(!in_array($args->order_target, array('list_order','update_order'))) $args->order_target = 'list_order';
            // 정렬 순서
            if(!in_array($args->order_type, array('asc','desc'))) $args->order_type = 'asc';

            // 페이지 수
            $args->page_count = (int)$args->page_count;
            if(!$args->page_count) $args->page_count = 1;

            // 출력된 목록 수
            $args->list_count = (int)$args->list_count;
            if(!$args->list_count) $args->list_count = 5;

            // 썸네일 컬럼 수
            $args->cols_list_count = (int)$args->cols_list_count;
            if(!$args->cols_list_count) $args->cols_list_count = 4;

            // 제목 길이 자르기
            if(!$args->subject_cut_size) $args->subject_cut_size = 0;
            // 내용 길이 자르기
            if(!$args->content_cut_size) $args->content_cut_size = 100;

            // 최근 글 표시 시간
            if(!$args->duration_new) $args->duration_new = 12;
            // 썸네일 생성 방법
            if(!$args->thumbnail_type) $args->thumbnail_type = 'crop';
            // 썸네일 가로 크기
            if(!$args->thumbnail_width) $args->thumbnail_width = 100;
            // 썸네일 세로 크기
            if(!$args->thumbnail_height) $args->thumbnail_height = 75;




            // rss 인경우는 다르다
            if($args->content_type == 'rss'){
                $args->rss_urls = array();
                $rss_urls = array_unique(array($args->rss_url0,$args->rss_url1,$args->rss_url2,$args->rss_url3,$args->rss_url4));
                for($i=0,$c=count($rss_urls);$i<$c;$i++) {
                    if($rss_urls[$i]) $args->rss_urls[] = $rss_urls[$i];
                }
                $args->mid_lists = array();
            }else{
                $oModuleModel = &getModel('module');
                // 대상 모듈 (mid_list는 기존 위젯의 호환을 위해서 처리하는 루틴을 유지. module_srl로 위젯에서 변경)
                if($args->mid_list) {
                    $mid_list = explode(",",$args->mid_list);

                    if(count($mid_list)) {
                        $module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
                    } else {
                        $site_module_info = Context::get('site_module_info');
                        if($site_module_info) {
                            $margs->site_srl = $site_module_info->site_srl;
                            $oModuleModel = &getModel('module');
                            $output = $oModuleModel->getMidList($margs);
                            if(count($output)) $mid_list = array_keys($output);
                            $module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
                        }
                    }
                } else {
                    $module_srl = explode(',',$args->module_srls);
                }

                if(is_array($module_srl)) $args->module_srl = implode(',',$module_srl);
                else $args->module_srl = $module_srl;


                if(!$args->module_srls){
                    // 대상 모듈이 선택되어 있지 않으면 해당 사이트의 전체 모듈을 대상으로 함
                    $site_module_info = Context::get('site_module_info');
                    if($site_module_info){
                        $s_obj->site_srl = (int)$site_module_info->site_srl;
                    }
                
                    $mid_list = $oModuleModel->getMidList($s_obj);
                    foreach($mid_list as $mid => $module){
                        $args->module_srl[] = $module->module_srl;
                    }
                }



                // 각 모듈의 정보를 가져온다
                $selected_modules_info = $oModuleModel->getModulesInfo($args->module_srl);
                $args->mid_lists = array();
                $args->modules_info = array();
                foreach($selected_modules_info as $key => $module_info){
                    $args->modules_info[$module_info->mid] = $module_info;
                    $args->mid_lists[$module_info->module_srl] = $module_info->mid;
                }
            }

            $option_view = array();
            $args->option_view_arr = explode(',',$args->option_view);
            switch($args->content_type){
                default:
                case 'document':
                    $content_items = $this->getDocumentItems($args);
                break;
                case 'comment':
                    $content_items = $this->getCommentItems($args);
                break;
                case 'image':
                    $content_items = $this->getImageItems($args);
                break;
                case 'rss':
                    set_include_path("./libs/PEAR");
                    require_once('PEAR.php');
                    require_once('HTTP/Request.php');
                    $content_items = $this->getRssItems($args);
                break;
                case 'trackback':
                    $content_items = $this->getTrackbackItems($args);
                break;
            }


            $output = $this->_complie($args,$content_items);
            return $output;

        }


        function _complie($args,$content_items){

            // 위젯에 넘기기 위한 변수 설정
            $widget_info->modules_info = $args->modules_info;
            $widget_info->option_view_arr = $args->option_view_arr;
            $widget_info->list_count = $args->list_count;
            $widget_info->page_count = $args->page_count;
            $widget_info->subject_cut_size = $args->subject_cut_size;
            $widget_info->content_cut_size = $args->content_cut_size;

            $widget_info->duration_new = $duration_new * 60*60;
            $widget_info->thumbnail_type = $args->thumbnail_type;
            $widget_info->thumbnail_width = $args->thumbnail_width;
            $widget_info->thumbnail_height = $args->thumbnail_height;
            $widget_info->cols_list_count = $args->cols_list_count;
            $widget_info->mid_lists = $args->mid_lists;

            $widget_info->show_browser_title = $args->show_browser_title;
            $widget_info->show_category = $args->show_category;
            $widget_info->show_comment_count = $args->show_comment_count;
            $widget_info->show_trackback_count = $args->show_trackback_count;
            $widget_info->show_icon = $args->show_icon;

            unset($args->option_view_arr);
            unset($args->modules_info);

            Context::set('colorset', $args->colorset);
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            $oTemplate = &TemplateHandler::getInstance();

            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){
                $widget_info->content_items = $content_items;
                Context::set('widget_info', $widget_info);
                $output = $oTemplate->compile($tpl_path, $args->list_type);
            }else{

                $content = array();
                $tab = array();
                // 각각별 컴파일은 한다
                foreach($args->mid_lists as $module_srl => $mid){
                    if(count($content_items[$module_srl]) > 0){
                        $widget_info->content_items = $content_items[$module_srl];
                        if(is_array($content_items[$module_srl]) && count($content_items[$module_srl])>0){
                            Context::set('widget_info', $widget_info);
                            $tab[$module_srl]->title = $content_items[$module_srl][0]->getBrowserTitle();
                            $tab[$module_srl]->content = $oTemplate->compile($tpl_path, $args->list_type);

                            // rss 의 경우 링크가 다르다
                            $tab[$module_srl]->url = $content_items[$module_srl][0]->getContentsLink();
                            if(!$tab[$module_srl]->url) $tab[$module_srl]->url = getUrl('').$mid;

                        }
                    }
                }

                unset($args->mid_lists);

                Context::set('_tab', $tab);
                $output = $oTemplate->compile($tpl_path, '_' . $args->tab_type);
            }

            return $output;
        }


        // 최근글
        function getDocumentItems($args) {
            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){
                $content_items = $this->_getDocumentItems($args);
            }else{
                $content_items = array();
                foreach($args->mid_lists as $module_srl => $mid){
                    $args->module_srl = $module_srl;
                    $content_items[$module_srl] = $this->_getDocumentItems($args);
                }

            }

            return $content_items;
        }



        function _getDocumentItems($args){
            $obj->module_srl = $args->module_srl;
            $obj->sort_index = 'documents.'.$args->order_target;
            $obj->order_type = $args->order_type=="desc"?"asc":"desc";
            $obj->list_count = $args->list_count * $args->page_count;

            $output = executeQueryArray('widgets.content.getNewestDocuments', $obj);

            // 오류가 생기면 그냥 무시
            if(!$output->toBool()) return;

            // document 모듈의 model 객체를 받아서 결과를 객체화 시킴
            $oDocumentModel = &getModel('document');

            // 분류를 먼저 구하자
            $category_lists = array();
            foreach($args->mid_lists as $module_srl => $mid){
                $category_lists[$module_srl] = $oDocumentModel->getCategoryList($module_srl);
            }

            // 결과가 있으면 각 문서 객체화를 시킴
            $content_items = array();
            $first_thumbnail_item = null;
            if(count($output->data)) {
                foreach($output->data as $key => $attribute) {

                    $oDocument = new documentItem();
                    $oDocument->setAttribute($attribute);

                    $browser_title = $args->modules_info[$attribute->mid]->browser_title;
                    $content_item = new contentItem($browser_title);

                    // 기본 set
                    $content_item->adds($attribute);

                    // 분류
                    $category = $category_lists[$attribute->module_srl][$attribute->category_srl]->text;
                    $content_item->setCategory($category);

                    $content = $oDocument->getSummary($args->content_cut_size);
                    $content_item->setContent($content);

                    // 링크
                    $url = sprintf("%s#%s",$oDocument->getPermanentUrl() ,$oDocument->getCommentCount());
                    $content_item->setLink($url);

                    //섬네일
                    $thumbnail = $oDocument->getThumbnail($args->thumbnail_width,$args->thumbnail_height,$args->thumbnail_type);
                    $content_item->setThumbnail($thumbnail);
                    if(is_null($first_thumbnail_item) && $thumbnail) $first_thumbnail_item = $key;

                    // 아이콘이미지
                    $extra_images = $oDocument->printExtraImages($args->duration_new);
                    $content_item->setExtraImages($extra_images);

                    $content_items[] = $content_item;
                }

                $content_items[0]->setFirstThumbnailIndex($first_thumbnail_item);
            }
            return $content_items;
        }

        // 댓글
        function getCommentItems($args) {

            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){
                $content_items = $this->_getCommentItems($args);
            }else{
                $content_items = array();
                foreach($args->mid_lists as $module_srl => $mid){
                    $args->module_srl = $module_srl;
                    $content_items[$module_srl] = $this->_getCommentItems($args);
                }
            }
            return $content_items;
        }

        function _getCommentItems($args) {

            // CommentModel::getCommentList()를 이용하기 위한 변수 정리
            $obj->module_srl = $args->module_srl;
            $obj->sort_index = $args->order_target;
            $obj->list_count = $args->list_count;

            // comment 모듈의 model 객체를 받아서 getCommentList() method를 실행
            $oCommentModel = &getModel('comment');
            $output = $oCommentModel->getNewestCommentList($obj);
            $content_items = array();

            if(count($output)) {
                foreach($output as $key => $oComment) {

                    $attribute = $oComment->getObjectVars();

                    $attribute->mid = $args->mid_lists[$attribute->module_srl];
                    $browser_title = $args->modules_info[$attribute->mid]->browser_title;

                    $content_item = new contentItem($browser_title);

                    // 기본 set
                    $content_item->adds($attribute);

                    // 제목
                    $title = $oComment->getSummary($args->content_cut_size);
                    $content_item->setTitle($title);

                    //섬네일
                    $thumbnail = $oComment->getThumbnail($args->thumbnail_width,$args->thumbnail_height,$args->thumbnail_type);
                    $content_item->setThumbnail($thumbnail);

                    // 링크
                    $url = sprintf("%s#comment_%s",getUrl('','document_srl',$oComment->get('document_srl')),$oComment->get('comment_srl'));
                    $content_item->setLink($url);

                    $content_items[] = $content_item;
                }
            }

            return $content_items;
        }

        // 이미지
        function getImageItems($args) {
            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){
                $content_items = $this->_getImageItems($args);
            }else{
                $content_items = array();
                foreach($args->mid_lists as $module_srl => $mid){
                    $args->module_srl = $module_srl;
                    $content_items[$module_srl] = $this->_getImageItems($args);
                }
            }
            return $content_items;
        }

        function _getImageItems($args) {

            $obj->module_srl = $args->module_srl;
            $obj->direct_download = 'Y';
            $obj->isvalid = 'Y';
            $oDocumentModel = &getModel('document');


            // 분류를 먼저 구하자
            $category_lists = array();
            foreach($args->mid_lists as $module_srl => $mid){
                $category_lists[$module_srl] = $oDocumentModel->getCategoryList($module_srl);
            }


            // 정해진 모듈에서 문서별 파일 목록을 구함
 //           $obj->list_count = $args->rows_list_count*$args->cols_list_count;
            $obj->list_count = $args->list_count;
            $files_output = executeQueryArray("file.getOneFileInDocument", $obj);
            $files_count = count($files_output->data);

            $content_items = array();

            if($files_count>0) {
                for($i=0;$i<$files_count;$i++) $document_srl_list[] = $files_output->data[$i]->document_srl;
                $tmp_document_list = $oDocumentModel->getDocuments($document_srl_list);
                if(count($tmp_document_list)) {
                    foreach($tmp_document_list as $oDocument){

                        $attribute = $oDocument->getObjectVars();
                        $browser_title = $args->modules_info[$attribute->mid]->browser_title;
                        $content_item = new contentItem($browser_title);

                        // 기본 set
                        $content_item->adds($attribute);

                        // 분류
                        $category = $category_lists[$attribute->module_srl]->text;
                        $content_item->setCategory($category);


                        $content = $oDocument->getSummary($args->content_cut_size);
                        $content_item->setContent($content);


                        // 링크
                        $url = sprintf("%s#%s",$oDocument->getPermanentUrl() ,$oDocument->getCommentCount());
                        $content_item->setLink($url);


                        //섬네일
                        $thumbnail = $oDocument->getThumbnail($args->thumbnail_width,$args->thumbnail_height,$args->thumbnail_type);
                        $content_item->setThumbnail($thumbnail);


                        // 아이콘이미지
                        $extra_images = $oDocument->printExtraImages($args->duration_new);
                        $content_item->setExtraImages($extra_images);

                        $content_items[] = $content_item;
                    }
                }
            }

            return $content_items;
        }


        // RSS
        function getRssItems($args){
            // 탭이든 아니든 다 가져와야한다
            $content_items = array();
            $args->mid_lists = array();
            foreach($args->rss_urls as $key => $rss){

                $args->rss_url = $rss;
                $content_item = $this->_getRssItems($args);
                if(count($content_item) > 0){
                    $browser_title = $content_item[0]->getBrowserTitle();

                    $args->mid_lists[] = $browser_title;
                    $content_items[] = $content_item;
                }
            }

            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){

                // 아이템들을 다 모으고
                $items = array();
                foreach($content_items as $key => $val){
                    foreach($val as $k => $v){
                        $date = $v->get('regdate');
                        $i=0;
                        while(array_key_exists(sprintf('%s%02d',$date,$i), $items)) $i++;
                        $items[sprintf('%s%02d',$date,$i)] = $v;
                    }
                }

                // 정렬을 하자
                if($args->order_type =='asc') ksort($items);
                else krsort($items);
                // list_count 만큼 위에서 부터 자른다
                $content_items = array_slice(array_values($items),0,$args->list_count);
            }else{

                foreach($content_items as $key=> $content_item_list){
                    $items = array();
                    foreach($content_item_list as $k => $content_item){
                        $date = $content_item->get('regdate');
                        $i=0;
                        while(array_key_exists(sprintf('%s%02d',$date,$i), $items)) $i++;
                        $items[sprintf('%s%02d',$date,$i)] = $content_item;
                    }
                    if($args->order_type =='asc') ksort($items);
                    else krsort($items);

                    $content_items[$key] = array_values($items);
                }
            }
            return $content_items;
        }

        function _getRssItems($args){

            // 날짜 형태
            $DATE_FORMAT = $args->date_format ? $args->date_format : "Y-m-d H:i:s";

            // request rss
            $args->rss_url = Context::convertEncodingStr($args->rss_url);
            $URL_parsed = parse_url($args->rss_url);
            if(strpos($URL_parsed["host"],'naver.com')) $args->rss_url = iconv('UTF-8', 'euc-kr', $args->rss_url);
            $args->rss_url = str_replace(array('%2F','%3F','%3A','%3D','%3B','%26'),array('/','?',':','=',';','&'),urlencode($args->rss_url));
            $URL_parsed = parse_url($args->rss_url);

            $host = $URL_parsed["host"];
            $port = $URL_parsed["port"];

            if ($port == 0) $port = 80;

            $path = $URL_parsed["path"];

            if ($URL_parsed["query"] != '') $path .= "?".$URL_parsed["query"];

            $oReqeust = new HTTP_Request($args->rss_url);
            $oReqeust->addHeader('Content-Type', 'application/xml');
            $oReqeust->setMethod('GET');

            $user = $URL_parsed["user"];
            $pass = $URL_parsed["pass"];

            if($user) $oReqeust->setBasicAuth($user, $pass);

            $oResponse = $oReqeust->sendRequest();
            if (PEAR::isError($oResponse)) {
                return new Object(-1, 'msg_fail_to_request_open');
            }
            $buff = $oReqeust->getResponseBody();
            $encoding = preg_match("/<\?xml.*encoding=\"(.+)\".*\?>/i", $buff, $matches);
            if($encoding && !preg_match("/UTF-8/i", $matches[1])) $buff = trim(iconv($matches[1]=="ks_c_5601-1987"?"EUC-KR":$matches[1], "UTF-8", $buff));

            $buff = preg_replace("/<\?xml.*\?>/i", "", $buff);



            $oXmlParser = new XmlParser();
            $xml_doc = $oXmlParser->parse($buff);
            $rss->title = $xml_doc->rss->channel->title->body;
            $rss->link = $xml_doc->rss->channel->link->body;

            $items = $xml_doc->rss->channel->item;

            if(!$items) return;
            if($items && !is_array($items)) $items = array($items);

            $content_items = array();

            foreach ($items as $key => $value) {
                if($key >= $args->list_count * $args->page_count) break;
                unset($item);

                foreach($value as $key2 => $value2) {
                    if(is_array($value2)) $value2 = array_shift($value2);
                    $item->{$key2} = $value2->body;
                }

                $content_item = new contentItem($rss->title);
                $content_item->setContentsLink($rss->link);
                $content_item->setTitle($item->title);
                $content_item->setNickName(max($item->author,$item->{'dc:creator'}));
                $content_item->setCategory($item->category);
                $item->description = preg_replace('!<a href=!is','<a onclick="window.open(this.href);return false" href=', $item->description);
                $content_item->setContent($item->description);
                $content_item->setLink($item->link);
                $date = date('YmdHis', strtotime(max($item->pubdate,$item->pubDate,$item->{'dc:date'})));
                $content_item->setRegdate($date);

                $content_items[] = $content_item;
            }
            return $content_items;
        }


        // Trackback
        function getTrackbackItems($args){

            $module_srls = explode(',',$args->module_srl);

            // 탭형태가 아니다
            if($args->tab_type == 'none' || $args->tab_type == ''){
                $content_items = $this->_getTrackbackItems($args);
            }else{
                $content_items = array();
                foreach($args->mid_lists as $module_srl => $mid){
                    $args->module_srl = $module_srl;
                    $content_items[$module_srl] = $this->_getTrackbackItems($args);
                }
            }
            return $content_items;

        }

        function _getTrackbackItems($args){
            $obj->module_srl = $args->module_srl;
            $obj->sort_index = $args->order_target;
            $obj->list_count = $args->list_count;

            // trackback 모듈의 model 객체를 받아서 getTrackbackList() method를 실행
            $oTrackbackModel = &getModel('trackback');
            $output = $oTrackbackModel->getNewestTrackbackList($obj);

            // 오류가 생기면 그냥 무시
            if(!$output->toBool()) return;

            // 결과가 있으면 각 문서 객체화를 시킴
            $content_items = array();
            if(count($output->data)) {
                foreach($output->data as $key => $item) {
                    $content_item = new contentItem();
                    $content_item->setTitle($item->title);
                    $content_item->setNickName($item->blog_name);
                    $content_item->setContent($item->excerpt);
                    $content_item->setLink($item->url);
                    $content_item->setRegdate($item->regdate);
                    $content_items[] = $content_item;
                }
            }
            return $content_items;
        }
    }









    class contentItem extends Object {

        var $browser_title = null;
        var $first_thumbnail_index = null;
        var $contents_link = null;

        function contentItem($browser_title=''){
            $this->browser_title = $browser_title;
        }

        function setContentsLink($link){
            $this->contents_link = $link;
        }

        function setFirstThumbnailIndex($first_thumbnail_index){
            if(!is_null($first_thumbnail_index)) $this->first_thumbnail_index = $first_thumbnail_index;
        }

        function setExtraImages($extra_images){
            $this->add('extra_images',$extra_images);
        }

        function setLink($url){
            $this->add('url',$url);
        }
        function setTitle($title){
            $this->add('title',$title);
        }

        function setThumbnail($thumbnail){
            $this->add('thumbnail',$thumbnail);
        }
        function setContent($content){
            $this->add('content',$content);
        }
        function setRegdate($regdate){
            $this->add('regdate',$regdate);
        }
        function setNickName($nick_name){
            $this->add('nick_name',$nick_name);
        }
        function setCategory($category){
            $this->add('category',$category);
        }

        function getBrowserTitle(){
            return $this->browser_title;
        }
        function getContentsLink(){
            return $this->contents_link;
        }

        function getFirstThumbnailIndex(){
            return $this->first_thumbnail_index;
        }

        function getLink(){
            return $this->get('url');
        }
        function getModuleSrl(){
            return $this->get('module_srl');
        }
        function getTitle($cut_size = 0, $tail='...'){
            if($cut_size) $title = cut_str($this->get('title'), $cut_size, $tail);
            else $title = $this->get('title');
            return $title;
        }
        function getContent(){
            return $this->get('content');
        }
        function getCategory(){
            return $this->get('category');
        }
        function getNickName(){
            return $this->get('nick_name');
        }
        function getCommentCount(){
            $comment_count = $this->get('comment_count');
            return $comment_count>0 ? $comment_count : '';
        }
        function getTrackbackCount(){
            $trackback_count = $this->get('trackback_count');
            return $trackback_count>0 ? $trackback_count : '';
        }
        function getRegdate($format = 'Y.m.d H:i:s') {
            return zdate($this->get('regdate'), $format);
        }
        function printExtraImages() {
            return $this->get('extra_images');
        }
        function getThumbnail(){
            return $this->get('thumbnail');
        }
    }
?>