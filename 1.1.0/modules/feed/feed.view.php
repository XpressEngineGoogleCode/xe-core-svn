<?php
/**
 * @class  feedView
 * @author zero (zero@nzeo.com)
 * @brief  feed module의 view class
 *
 * RSS 2.0형식으로 문서 출력
 *
 **/

class feedView extends feed {

    /**
     * @brief 초기화
     **/
    function init() {
    }

    /**
     * @brief RSS 출력
     **/
    function rss() {
        Context::setResponseMethod('Feed');

        $oFeedModel = &getModel('feed');
        $document_list = $oFeedModel->getFeedDodumentList();

        // feed 제목 및 정보등을 추출
        if($this->mid) {
            $info->title = Context::getBrowserTitle();
            $info->description = $this->module_info->description;
            $info->link = getUrl('', 'mid', Context::get('mid'));
        } else {
            $info->title = $info->link = Context::getRequestUri();
        }

        $info->total_count = $output->total_count;
        $info->total_page = $output->total_page;
        $info->date = date('D, d M Y H:i:s').' '.$GLOBALS['_time_zone'];
        $info->language = Context::getLangType();

        // RSS 출력물에서 사용될 변수 세팅
        Context::set('info', $info);
        Context::set('mid_list', $mid_list);
        Context::set('document_list', $document_list);

        // 결과물을 얻어와서 에디터 컴포넌트등의 전처리 기능을 수행시킴
        $path = $this->module_path.'tpl/';
        if($args->start_date || $args->end_date) $file = 'xe_rss';
        else $file = 'rss20';

        $oTemplate = new TemplateHandler();
        $oContext = &Context::getInstance();

        $content = $oTemplate->compile($path, $file);
        $content = $oContext->transContent($content);
        Context::set('content', $content);

        // 템플릿 파일 지정
        $this->setTemplatePath($path);
        $this->setTemplateFile($file);
    }


    /**
     * @brief RSS 출력
     **/
    function atom() {
        Context::setResponseMethod('Feed');

        $oFeedModel = &getModel('feed');
        $document_list = $oFeedModel->getFeedDodumentList();

        // feed 제목 및 정보등을 추출
        if($this->mid) {
            $info->title = Context::getBrowserTitle();
            $info->description = $this->module_info->description;
            $info->link = getUrl('','mid',Context::get('mid'));
        } else {
            $info->title = $info->link = Context::getRequestUri();
        }
        $info->total_count = $output->total_count;
        $info->total_page = $output->total_page;
        $info->date = date('D, d M Y H:i:s').' '.$GLOBALS['_time_zone'];
        $info->language = Context::getLangType();

        // RSS 출력물에서 사용될 변수 세팅
        Context::set('info', $info);
        Context::set('mid_list', $mid_list);
        Context::set('document_list', $document_list);

        // 결과물을 얻어와서 에디터 컴포넌트등의 전처리 기능을 수행시킴
        $path = $this->module_path.'tpl/';
        if($args->start_date || $args->end_date) $file = 'xe_feed';
        else $file = 'atom10';

        $oTemplate = new TemplateHandler();
        $oContext = &Context::getInstance();

        $content = $oTemplate->compile($path, $file);
        $content = $oContext->transContent($content);

        Context::set('content', $content);

        // 템플릿 파일 지정
        $this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile('atom10');
    }


    /**
     * @brief 에러 출력
     **/
    function dispError() {

        // 결과 출력을 XMLRPC로 강제 지정
        Context::setResponseMethod("XMLRPC");

        // 출력 메세지 작성
        Context::set('error', -1);
        Context::set('message', Context::getLang('msg_feed_is_disabled') );

        // 템플릿 파일 지정
        $this->setTemplatePath($this->module_path.'tpl');
        $this->setTemplateFile("error");
    }

    /**
     * @brief 서비스형 모듈의 추가 설정을 위한 부분
     * feed의 사용 형태에 대한 설정만 받음
     **/
    function triggerDispFeedAdditionSetup(&$obj) {
        $current_module_srl = Context::get('module_srl');
        $current_module_srls = Context::get('module_srls');

        if(!$current_module_srl && !$current_module_srls) {
            // 선택된 모듈의 정보를 가져옴
            $current_module_info = Context::get('current_module_info');
            $current_module_srl = $current_module_info->module_srl;
            if(!$current_module_srl) return new Object();
        }

        // 선택된 모듈의 feed설정을 가져옴
        $oFeedModel = &getModel('feed');
        $feed_config = $oFeedModel->getFeedModuleConfig($current_module_srl);
        Context::set('feed_config', $feed_config);

        // 템플릿 파일 지정
        $oTemplate = &TemplateHandler::getInstance();
        $tpl = $oTemplate->compile($this->module_path.'tpl', 'feed_module_config');
        $obj .= $tpl;

        return new Object();
    }
}
?>