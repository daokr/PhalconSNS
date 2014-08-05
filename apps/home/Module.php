<?php
namespace IKPHP\Apps\Home;
class Module 
{
        public function registerAutoloaders()
        {

                $loader = new \Phalcon\Loader();

                $loader->registerNamespaces(array(
                        'IKPHP\Apps\Home\Controllers' => __DIR__ . '/controllers/',
                        'IKPHP\Apps\Home\Models' => __DIR__ . '/models/',
                		//在该模块下添加其他App的命名空间	
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
                        $view->setTemplateAfter('main');
                        $view->registerEngines(array(
							".html" => 'volt'
						));
                        return $view;
                });                

        }

}