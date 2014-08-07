<?php
namespace IKPHP\Apps\Home\Models;

use IKPHP\Common\Models\BaseModel;

class UserScoreLog extends BaseModel
{

    public function getSource()
    {
        return IK."user_score_log";
    } 
}
