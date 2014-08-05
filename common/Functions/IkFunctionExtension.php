<?php
namespace IKPHP\Common\Functions;
class IkFunctionExtension
{
    /**
     * 调用此方法对任何视图编译函数调用
     */
    public function compileFunction($name, $arguments)
    {
        if (function_exists($name)) {
        	return $name . '('. $arguments . ')';
        }
    }

}