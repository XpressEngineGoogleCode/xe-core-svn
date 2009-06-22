<?php
    /**
     * @class  rssController
     * @author zero (zero@nzeo.com)
     * @brief  rss module의 controller class
     *
     * Feed 로 문서 출력
     *
     **/

    class rssController extends rss {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief RSS 사용 유무를 체크하여 rss url 추가
         **/
        function triggerRssUrlInsert() {
            $oModuleModel = &getModel('module');
            $total_config = $oModuleModel->getModuleConfig('rss');
            $current_module_srl = Context::get('module_srl');
            $site_module_info = Context::get('site_module_info');

            if(!$current_module_srl) {
                $current_module_info = Context::get('current_module_info');
                $current_module_srl = $current_module_info->module_srl;
            }

            if(!$current_module_srl) return new Object();

            // 선택된 모듈의 rss설정을 가져옴
            $oRssModel = &getModel('rss');
            $rss_config = $oRssModel->getRssModuleConfig($current_module_srl);

            if($rss_config->open_rss != 'N') {
                if(Context::isAllowRewrite()) {
                    $request_uri = Context::getRequestUri();
                    // 가상 사이트 변수가 있고 이 변수가 mid와 다를때. (vid와 mid는 같을 수 없다고 함)
                    if(Context::get('vid') && Context::get('vid') != Context::get('mid')) {
                        Context::set('rss_url', Context::getRequestUri().Context::get('vid').'/'.Context::get('mid').'/rss');
                        Context::set('atom_url', Context::getRequestUri().Context::get('vid').'/'.Context::get('mid').'/atom');
                    }
                    else {
                        Context::set('rss_url', $request_uri.Context::get('mid').'/rss');
                        Context::set('atom_url', $request_uri.Context::get('mid').'/atom');
                    }
                }
                else {
                    Context::set('rss_url', getUrl('','mid',Context::get('mid'),'act','rss'));
                    Context::set('atom_url', getUrl('','mid',Context::get('mid'),'act','atom'));
                }
            }

            if(Context::isInstalled() && $site_module_info->mid == Context::get('mid') && $total_config->use_total_feed != 'N') {
                if(Context::isAllowRewrite() && !Context::get('vid')) {
                    $request_uri = Context::getRequestUri();
                    Context::set('general_rss_url', $request_uri.'rss');
                    Context::set('general_atom_url', $request_uri.'atom');
                }
                else {
                    Context::set('general_rss_url', getUrl('','module','rss','act','rss'));
                    Context::set('general_atom_url', getUrl('','module','rss','act','atom'));
                }
            }

            return new Object();
        }
    }
?>
