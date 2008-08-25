<?php
/**
 * @breif PSM에서 지정된 변수들을 저장할 전역 변수
 **/
$PSM_var = array();

/**
 * @class  PSM
 * @author HNO3 (wdlee91@gmail.com)
 * @brief  php4에서 static member 변수 흉내
 *
 * zbxe에서는 현재 php4도 지원 상태에 있기에, php4에서 지원하지 않는 static member 변수를 흉내낼 수 있도록 하는 class.
 * 개발 플랫폼이 php5 전용으로 넘어가면 이 클래스도 삭제될 것이다.
 * 사용 예:
 * <pre style="margin-left: 3em;"><code>class Foo {
 *     function bar() {
 *         $someVar = &PSM::v('someVar');
 *         if($someVar === null) $someVar = 1; // initializing
 *         // use $someVar like Foo::$someVar
 *     }
 * }
 * $FooSomeVar = &PSM::vc('Foo', 'someVar');
 * // use $FooSomeVar like Foo::$someVar</code></pre>
 **/
class PSM // Pseudo Static Member
{
    /**
     * @brief 함수를 호출한 class 이름을 얻어옴.
     **/
    function getCallerClass()
    {
        $trace = debug_backtrace();

        if(!isset($trace[2]))
            return null;

        if(empty($trace[2]['class']))
            return null;

        return strtolower($trace[2]['class']);
    }

    /**
     * @brief class 내에서 자신의 클래스에 귀속된 PSM 변수를 얻어옴.
     * @param name 변수 이름
     **/
    function &v($name)
    {
        global $PSM_var;

        $class = PSM::getCallerClass();
        if(!$class) // Caller is a global function
            $class = '__global';

        if(!isset($PSM_var[$class][$name]))
            $PSM_var[$class][$name] = null;

        return $PSM_var[$class][$name];
    }

    /**
     * @brief class 밖 또는 다른 클래스에 귀속된 PSM 변수를 얻어옴.
     * @param class 귀속된 클래스 이름
     * @param name 변수 이름
     **/
    function &vc($class, $name)
    {
        global $PSM_var;

        $class = strtolower($class);

        if(!isset($PSM_var[$class][$name]))
            $PSM_var[$class][$name] = null;

        return $PSM_var[$class][$name];
    }
}
?>