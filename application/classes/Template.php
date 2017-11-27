<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * 在不影响原有模板体系的情况下,植入SMARTY模板引擎
 * 配置文件: config/smarty.php
 *
 * 模版除了标签语法采用smarty3以外,其他全部可参考KOHANA VIEW引擎的调用方式  http://kohanaframework.org/3.3/guide/kohana/mvc/views
 * 增加response方法: 可以直接输出结果并且结束PHP程序,不需要传递给kohana的response
 * Template::factory('Admin/Permission')->response(array('title'=>'例子', 'content'=>'内容'));
 * 增加dislplay方法: 直接输出渲染后的页面代码,不需要kohana的response
 * Template::factory('Admin/Permission')->display(array('title'=>'例子', 'content'=>'内容'));
 * 增加message方法: 用于信息提示页面的输出
 * Template::factory()->message(array('type'=>'error','title'=>'标题','message'=>'提示内容……', 'back'=>true,'redirect'=>'http://www.baidu.com','redirect_time'=>5));
 * type提示类型:error 错误,success 成功,notice 提醒,info 普通信息.
 *
 * 模板类实例化是会自动读取:config/smarty.php
 *
 */

class Template
{
    protected $smarty;
    protected $data = array();
    protected $file = NULL;
    protected static $global_data = array();
    protected static $file_subfix = '.tpl';

    public static function factory($file='', $data = array()) 
    {
        return new Template($file, $data);
    }

    public function __construct($file = NULL, array $data = NULL) 
    {
        $this->smarty = self::smarty();
        if($file){
            $this->set_filename($file);
        }
        if($data){
            $this->set($data);
        }

    }

    public static function smarty()
    {
        $config = Kohana::$config->load('smarty')->get('default');
        if(!isset($config['template_dir']) || !isset($config['compile_dir']) || !isset($config['cache_dir']) ) {
            echo 'smarty config is null.';
            exit;
        }
        $obj = new Smarty();
        $obj->setDebugging($config['debugging']);
        $obj->setCaching($config['caching']);
        $obj->cache_lifetime = $config['cache_lifetime'];
        $obj->setLeftDelimiter($config['left_delimiter'] ? $config['left_delimiter'] :'{{');
        $obj->setRightDelimiter($config['right_delimiter'] ? $config['right_delimiter'] :'}}');
        $obj->setTemplateDir($config['template_dir']);
        $obj->setCompileDir($config['compile_dir']);
        $obj->setCacheDir($config['cache_dir']);
        if($config['file_subfix']){
            self::$file_subfix = $config['file_subfix'];
        }
        if($config['template_vars']) {
            foreach ($config['template_vars'] as $k => $v) {
                $obj->assign($k,$v);
            }
        }

        return $obj;
    }

    public function set_filename($file) 
    {
        $this->file = $file;
        return $this;
    }

    public function set($key, $value = NULL) 
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->data[$name] = $value;
            }
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function bind($key='', & $value = NULL) 
    {
        $this->data[$key] = &$value;
    }

    public static function bind_global($key, & $value) 
    {
        self::$global_data[$key] =& $value;
    }

    public function render($file = NULL) 
    {
        if ($file !== NULL) {
            $this->set_filename($file);
        }
        if (empty($this->file)) {
            throw new View_Exception('You must set the file to use within your view before rendering');
        }
        $array = $this->data + self::$global_data;//合并全局模版变量
        return self::capture($this->file, $array);
    }

    public function & __get($key) 
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        elseif (array_key_exists($key, self::$global_data)) {
            return self::$global_data[$key];
        }
        else {
            throw new Kohana_Exception('View variable is not set: :var',
                array(':var' => $key));
        }
    }

    public function __isset($key)
    {
        return (isset($this->_data[$key]) OR isset(self::$global_data[$key]));
    }


    public function __set($key, $value) 
    {
        $this->set($key, $value);
    }


    public function __unset($key)
    {
        unset($this->data[$key] , self::$global_data[$key]);
        return $this;
    }

    public function __toString() 
    {
        try {
            return $this->render();
        }
        catch (Exception $e) {
            /**
             * Display the exception message.
             *
             * We use this method here because it's impossible to throw an
             * exception from __toString().
             */
            $error_response = Kohana_Exception::_handler($e);
            return $error_response->body();
        }
    }



    protected static function capture($tf, array $data = NULL) 
    {
        // Capture the view output
        ob_start();
        try {
            // Load the view within the current scope
            $smarty = self::smarty();
            foreach($data as $k => $v) {
                $smarty->assign($k, $v);
            }
            $smarty->display($tf.self::$file_subfix);
        }
        catch (Exception $e) {
            // Delete the output buffer
            ob_end_clean();
            // Re-throw the exception
            throw $e;
        }
        // Get the captured output and close the buffer
        return ob_get_clean();
    }



    //当时直接打印
    public function display($file = NULL,array $array = NULL)
    {
        if($array){
            $this->set($array);
        }
        echo $this->render($file) ;
    }


    //打印并结束
    public function response($file = NULL,array $array = NULL)
    {
        $this->display($file,$array);
        exit;
    }



    public function message($data=array()) 
    {
        if (!isset($data['title'])) {
            $data['title'] = "信息提示";
        }
        if (!isset($data['back'])) {
            $data['back'] = true;
        }
        if (!isset($data['type'])) {
            $data['type'] = 'error';
        }
        if (!isset($data['redirect'])) {
            $data['redirect'] = '';
        }
        if (!isset($data['redirect_time']) || $data['redirect_time'] < 1) {
            $data['redirect_time'] = 5;
        }
        $this->response('_Common/Message', $data);
    }


}
/**
 *模板中的一些部件简介
 * 模板存放于
 * /application/views/admin/smarty/
 * 模板CACHE /application/cache/smarty/cache
 * 编译文件存放 /application/cache/smarty/compile
 *
 */
