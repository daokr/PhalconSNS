<?php
// 注册模块
return array(
        'home' => array(
                'className' => 'IKPHP\Apps\Home\Module',
                'path' => __DIR__ . '/../apps/home/Module.php'
        ),
        'admin' => array(
                'className' => 'IKPHP\Apps\Admin\Module',
                'path' => __DIR__ . '/../apps/admin/Module.php'
        ),        
        'group' => array(
                'className' => 'IKPHP\Apps\Group\Module',
                'path' => __DIR__ . '/../apps/group/Module.php'
        ),
        'article' => array(
                'className' => 'IKPHP\Apps\Article\Module',
                'path' => __DIR__ . '/../apps/article/Module.php'
        ),
        'space' => array(
                'className' => 'IKPHP\Apps\Space\Module',
                'path' => __DIR__ . '/../apps/space/Module.php'
        ),                 
);
