<?php
    /**
     * @class ExtraVar
     * @author zero (zero@nzeo.com)
     * @brief 게시글, 회원등에서 사용하는 확장변수를 핸들링하는 클래스
     **/
    class ExtraVar {

        var $module_srl = null;

        /**
         * @brief constructor
         **/
        function &getInstance($module_srl) {
            static $oInstance = array();
            if(!$oInstance[$module_srl]) $oInstance[$module_srl] = new ExtraVar($module_srl);
            return $oInstance[$module_srl];
        }

        /**
         * @brief constructor
         **/
        function ExtraVar($module_srl) { 
            $this->module_srl = $module_srl;
        }

        /**
         * @brief 불필요한 등록을 피하기 위해서 특정 module_srl에 확장변수가 등록되었는지 확인
         **/
        function isSettedExtraVars() {
            return isset($GLOBALS['XE_EXTRAVARS'][$this->module_srl]);
        }

        /**
         * @brief 확장변수 키를 등록
         * php4를 대비해 class static 멤버변수 대신 $GLOBAL 변수 사용
         **/
        function setExtraVarKeys($extra_keys) {
            if(!$this->isSettedExtraVars()) {
                if(!$extra_keys || !count($extra_keys)) $GLOBALS['XE_EXTRAVARS'][$this->module_srl] = array();
                else {
                    if(!is_array($GLOBALS['XE_EXTRAVARS'][$this->module_srl])) $GLOBALS['XE_EXTRAVARS'][$this->module_srl] = array();
                    foreach($extra_keys as $key => $val) {
                        $obj = new ExtraItem($val->module_srl, $val->idx, $val->name, $val->type, $val->default, $val->desc, $val->is_required, $val->search, $val->value); 
                        $GLOBALS['XE_EXTRAVARS'][$this->module_srl][$val->idx] = $obj;
                    }
                }
            }
        }

        /**
         * @brief 확장변수 객체 배열 return
         **/
        function getExtraVars() {
            if(!$this->isSettedExtraVars()) return array();
            return $GLOBALS['XE_EXTRAVARS'][$this->module_srl];
        }
    }

    /**
     * @class ExtraItem
     * @author zero (zero@nzeo.com)
     * @brief 확장변수의 개별 값
     **/
    class ExtraItem {
        var $module_srl = 0;
        var $idx = 0;
        var $name = 0;
        var $type = 'text';
        var $default = null;
        var $desc = '';
        var $is_required = 'N';
        var $search = 'N';
        var $value = null;

        /**
         * @brief constructor
         **/
        function ExtraItem($module_srl, $idx, $name, $type = 'text', $default = null, $desc = '', $is_required = 'N', $search = 'N', $value = null) {
            if(!$idx) return;
            $this->module_srl = $module_srl;
            $this->idx = $idx;
            $this->name = $name;
            $this->type = $type;
            $this->default = $default;
            $this->desc = $desc;
            $this->is_required = $is_required;
            $this->search = $search;
            $this->value = $value;
        }

        /**
         * @brief 값 지정
         **/
        function setValue($value) {
            $this->value = $value;
        }

        /**
         * @brief type에 따라서 주어진 값을 변형하여 원형 값을 return
         **/
        function _getTypeValue($type, $value) {
            $value = trim($value);
            if(!trim($value)) return;
            switch($type) {
                case 'homepage' :
                        if(!preg_match('/^([a-z]+):\/\//i',$value)) $value = 'http://'.$value;
                        return htmlspecialchars($value);
                    break;
                case 'tel' :
                        if(is_array($value)) $values = $value;
                        elseif(strpos($value,'|@|')!==false) $values = explode('|@|', $value);
                        elseif(strpos($value,',')!==false) $values = explode(',', $value);
                        $values[0] = (int)($values[0]);
                        $values[1] = (int)($values[1]);
                        $values[2] = (int)($values[2]);
                        return $values;
                    break;
                    break;
                case 'checkbox' :
                case 'select' :
                        if(is_array($value)) $values = $value;
                        elseif(strpos($value,'|@|')!==false) $values = explode('|@|', $value);
                        elseif(strpos($value,',')!==false) $values = explode(',', $value);
                        else $values = array($value);
                        for($i=0;$i<count($values);$i++) $values[$i] = htmlspecialchars($values[$i]);
                        return $values;
                    break;
                //case 'date' :
                //case 'email_address' :
                //case 'text' :
                //case 'textarea' :
                default :
                        return htmlspecialchars($value);
                    break;
            }
        }

        /**
         * @brief 값을 return
         * 원형 값을 HTML 결과물로 return
         **/
        function getValueHTML() {
            $value = $this->_getTypeValue($this->type, $this->value);
            if(!$value) return;
            switch($this->type) {
                case 'homepage' :
                        return sprintf('<a href="%s" onclick="window.open(this.href); return false;">%s</a>', $value, $value);
                case 'email_address' :
                        return sprintf('<a href="mailto:%s">%s</a>', $value, $value);
                    break;
                case 'tel' :
                        return sprintf('%d - %d - %d', $value[0],$value[1],$value[2]);
                    break;
                case 'textarea' :
                        return nl2br($value);
                    break;
                case 'checkbox' :
                        if(is_array($value)) return implode(', ',$value);
                        else return $value;
                    break;
                case 'date' :
                        return zdate($value,"Y-m-d");
                    break;
                case 'select' :
                        if(is_array($value)) return implode(', ',$value);
                        else return $value;
                    break;
                //case 'text' :
                default :
                    return $value;
            }
        }

        /**
         * @brief type에 따른 form을 리턴
         **/
        function getFormHTML($className = 'inputTypeText') {
            $type = $this->type;
            $name = $this->name;
            $value = $this->_getTypeValue($this->type, $this->value);
            $default = $this->_getTypeValue($this->type, $this->default);
            $column_name = 'extra_vars'.$this->idx;

            $buff = '';
            switch($type) {
                // 홈페이지 주소
                case 'homepage' :
                        $buff .= '<input type="text" name="'.$column_name.'" value="'.$value.'" '.($className?'class="'.$className.'"':'').' />';
                    break;

                // Email 주소
                case 'email_address' :
                        $buff .= '<input type="text" name="'.$column_name.'" value="'.$value.'" '.($className?'class="'.$className.'"':'').' />';
                    break;

                // 전화번호
                case 'tel' :
                        $buff .= 
                            '<input type="text" name="'.$column_name.'" value="'.$value[0].'" size="4" '.($className?'class="'.$className.'"':'').' /> -'.
                            '<input type="text" name="'.$column_name.'" value="'.$value[1].'" size="4" '.($className?'class="'.$className.'"':'').' /> -'.
                            '<input type="text" name="'.$column_name.'" value="'.$value[2].'" size="4" '.($className?'class="'.$className.'"':'').' />';
                    break;

                // textarea
                case 'textarea' :
                        $buff .= '<textarea name="'.$column_name.'" '.($className?'class="'.$className.'"':'').'>'.$value.'</textarea>';
                    break;

                // 다중 선택
                case 'checkbox' :
                        $buff .= '<ul>';
                        foreach($default as $v) {
                            if($value && in_array($v, $value)) $checked = ' checked="checked"';
                            else $checked = '';
                            $buff .='<li><input type="checkbox" name="'.$column_name.'" value="'.htmlspecialchars($v).'" '.$checked.' />'.$v.'</li>';
                        }
                        $buff .= '</ul>';
                    break;

                // 단일 선택
                case 'select' :
                        $buff .= '<select name="'.$column_name.'">';
                        foreach($default as $v) {
                            if($value && in_array($v,$value)) $selected = ' selected="selected"';
                            else $selected = '';
                            $buff .= '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
                        }
                        $buff .= '</select>';
                    break;

                // 날짜 입력
                case 'date' :
                        // datepicker javascript plugin load
                        Context::loadJavascriptPlugin('ui.datepicker');

                        $buff .= 
                            '<input type="hidden" name="'.$column_name.'" value="'.$value.'" />'.
                            '<input type="text" id="date_'.$column_name.'" '.($className?'class="'.$className.'"':'').' value="'.zdate($value,'Y-m-d').'" readonly="readonly" />'."\n".
                            '<script type="text/javascript">'."\n".
                            '(function($){'."\n".
                            '    $(function(){'."\n".
                            '        var option = { gotoCurrent: false,yearRange:\'-100:+10\', onSelect:function(){'."\n".
                            '            $(this).prev(\'input[type="hidden"]\').val(this.value.replace(/-/g,""))}'."\n".
                            '        };'."\n".
                            '        $.extend(option,$.datepicker.regional[\''.Context::getLangType().'\']);'."\n".
                            '        $("#date_'.$column_name.'").datepicker(option);'."\n".
                            '    });'."\n".
                            '})(jQuery);'."\n".
                            '</script>';
                    break;

                // 일반 text
                default :
                        $buff .=' <input type="text" name="'.$column_name.'" value="'.$value.'" '.($className?'class="'.$className.'"':'').' />';
                    break;
            }
            if($this->desc) $buff .= '<p>'.$this->desc.'</p>';
            return $buff;
        }
    }
?>
