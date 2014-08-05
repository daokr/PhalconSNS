<?php
namespace IKPHP\Admin;
class Module 
{
        public function registerAutoloaders()
        {

                $loader = new \Phalcon\Loader();

                $loader->registerNamespaces(array(
                        'IKPHP\Admin\Controllers' => __DIR__ . '/controllers/',
                        'IKPHP\Admin\Models' => __DIR__ . '/models/',
						//需要调用其他模块APP的model和Controll请添加

                ));

                $loader->register();
        }

        public function registerServices($di)
        {

                $config = include ROOT_PATH . "/config/config.php";
                //定义模板文件位置目录
                $di->set('view', function() {
                        $view = new \Phalcon\Mvc\View();
                        $view->setViewsDir(__DIR__ . '/views/');                        $view->setViewsDir(__DIR__ . '/views/');
                        $view->setTemplateAfter('main');
                        $view->registerEngines(array(
							".html" => 'volt'
						));
                        return $view;
                });                


        }

}