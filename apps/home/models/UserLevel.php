<?php
namespace IKPHP\Apps\Home\Models;

use IKPHP\Common\Models\BaseModel;

class UserLevel extends BaseModel
{

    public function getSource()
    {
        return IK."user_role";
    } 
}
