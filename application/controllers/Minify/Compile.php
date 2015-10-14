<?php

/**
 * 编译CSS/JS
 *
 * @package Controller
 * @author  chengxuan <i@chengxuan.li>
 */
class Minify_CompileController extends AbsController {
    
    public function indexAction() {
        include APP_PATH . '/library/Thirdpart/Minify/bootstrap.php';
        
        $group_conf = \Model\Minify::loadGroupConfig();
        $sources = array();
        foreach($group_conf as $path => $files) {
            $this->_showSource($path, $files);
            echo "[Compile]: {$path}<br />\r\n";
        }
    }
    
    /**
     * 处理单条数据
     * 
     * @param string $path  路径
     * @param array  $files 文件列表
     */
    protected function _showSource($path, $files) {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $type_constant = 'Minify::TYPE_' . strtoupper($extension);
        if(defined($type_constant)) {
            $content_type = constant($type_constant);
        } else {
            $content_type = Minify::TYPE_HTML;
        }
        
        $sources = array();
        foreach($files as $value) {
        	$source_path = str_replace('//', ROOT_PATH . 'static/', $value);
        	if(!is_file($source_path)) {
        		echo "[Not Found] {$source_path}<br />\r\n";
        		continue;
        	}
        	$content = file_get_contents($source_path);
            $sources[] = new Minify_Source(array(
                'id' => $value,
                'content' => $content,
                'contentType' => $content_type
            ));
        }
        
        $minify = new Minify(new Minify_Cache_Null());
        $combined = $minify->combine($sources);
        
        $to_file_path = ROOT_PATH . "static/{$extension}/{$path}";
        $to_dir = dirname($to_file_path);
        if(!is_dir($to_dir)) {
        	mkdir($to_dir, 0775, true);
        }
        file_put_contents($to_file_path, $combined);
        
    }
}
