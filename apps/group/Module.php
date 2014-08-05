<?php
namespace IKPHP\Apps\Group;
class Module 
{
        public function registerAutoloaders()
        {

                $loader = new \Phalcon\Loader();

                $loader->registerNamespaces(array(
                        'IKPHP\Apps\Group\Controllers' => __DIR__ . '/controllers/',
                        'IKPHP\Apps\Group\Models' => __DIR__ . '/models/',
						//需要调用其他模块APP的model和Controll请添加
						//下面是调用home app 里的model
						//'IKPHP\Home\Models' => ROOT_PATH . '/apps/home/models/',
                ));

                $loader->register();
        }

        public function registerServices($di)
        {

                $config = include ROOT_PATH . "/config/config.php";
                //定义模板文件位置目录
                $di->set('view', function() {
                        $view = new \Phalcon\Mvc\View();
                        $view->setViewsDir(__DIR__ . '/views/');
                        //如果共享布局开启  如果不共享注释掉
                        $view->setLayoutsDir('../../home/views/layouts/');
                        $view->setTemplateAfter('main');
                        

                        $view->registerEngines(array(
							".html" => 'volt'
						));
                        return $view;
                });
            


        }

}