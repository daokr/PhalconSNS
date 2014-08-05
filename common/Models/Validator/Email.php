<?php 
namespace IKPHP\Common\Models\Validator;
use Phalcon\Mvc\Model\Validator,
	Phalcon\Mvc\Model\ValidatorInterface;

class Email extends Validator implements ValidatorInterface
{
    public function validate($model)
    {
        $field = $this->getOption('field');
        $value = $model->$field;
        if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$value)) {
            $this->appendMessage(
                "邮箱格式不正确",
                $field,
                "Email"
            );
            return false;
        }
        return true;
    }
}