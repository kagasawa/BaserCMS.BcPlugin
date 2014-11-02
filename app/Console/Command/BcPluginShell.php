<?php

/**
 * The BaserCMS Plugin creating an skelton plugin
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2014, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2014, baserCMS Users Community
 * @author          Hideyuki Kagasawa <kagasawa@web-prom.net>
 * @link			http://basercms.net baserCMS Project
 * @package			Baser.Console.Command
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */
/**
 * Include files
 */
App::uses('PluginTask', 'Console/Command/Task');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

/**
 * BcPlugin Shell
 * 
 * @package Baser.Console.Command
 */
class BcPluginShell extends PluginTask {

    public function main() {
        $this->execute();
    }

    /**
     * Bake the plugin, create directories and files
     *
     * @param string $plugin Name of the plugin in CamelCased format
     * @return bool
     */
    public function bake($plugin) {
        $pathOptions = App::path('plugins');
        if (count($pathOptions) > 1) {
            $this->findPath($pathOptions);
        }
        $this->hr();
        $this->out(__d('cake_console', "<info>Plugin Name:</info> %s", $plugin));
        $this->out(__d('cake_console', "<info>Plugin Directory:</info> %s", $this->path . $plugin));
        $this->hr();

        $looksGood = $this->in(__d('cake_console', 'Look okay?'), array('y', 'n', 'q'), 'y');
        
        if (strtolower($looksGood) === 'y') {
            $Folder = new Folder($this->path . $plugin);
            $directories = array(
                'Config' . DS . 'Schema',
                'Config' . DS . 'data' . DS . 'default',
                'Console' . DS . 'Command' . DS . 'Task',
                'Controller' . DS . 'Component',
                'Model' . DS . 'Behavior',
                'Model' . DS . 'Datasource',
                'Lib',
                'View' . DS . 'Helper',
                'View' . DS . $plugin,
                'View' . DS . $plugin . 'Configs' . DS . 'admin',
                'Test' . DS . 'Case' . DS . 'Controller' . DS . 'Component',
                'Test' . DS . 'Case' . DS . 'View' . DS . 'Helper',
                'Test' . DS . 'Case' . DS . 'Model' . DS . 'Behavior',
                'Test' . DS . 'Fixture',
                'Vendor',
                'webroot'
            );

            foreach ($directories as $directory) {
                $dirPath = $this->path . $plugin . DS . $directory;
                $Folder->create($dirPath);
                new File($dirPath . DS . 'empty', true);
            }

            foreach ($Folder->messages() as $message) {
                $this->out($message, 1, Shell::VERBOSE);
            }

            $errors = $Folder->errors();
            if (!empty($errors)) {
                foreach ($errors as $message) {
                    $this->error($message);
                }
                return false;
            }

            // アンダースコアのプラグイン名
            $_plugin = Inflector::underscore($plugin);
            
            // schema
            $fileName = $_plugin . '_configs.php';
            $out =<<<EOD
<?php 
class {$plugin}ConfigsSchema extends CakeSchema {
	public \$name = '{$plugin}Configs';

	public \$file = '{$_plugin}_configs.php';

	public \$connection = 'plugin';

	public function before(\$event = array()) {
		return true;
	}

	public function after(\$event = array()) {
	}

    public \${$_plugin}_configs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'value' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
}
EOD;

            $this->createFile($this->path . $plugin . DS . 'Config' . DS . 'Schema' . DS . $fileName, $out);

            // data
            $fileName = $_plugin . '_configs.csv';
            $out =<<<EOD
"id","name","value","created","modified"
"","message","おら権藤！禁煙するだ！吸ったら頭丸めるだ！","",""
EOD;
            $out = str_replace("\n", "\r\n", $out);
            $out = mb_convert_encoding($out, 'sjis', 'utf8');

            $this->createFile($this->path . $plugin . DS . 'Config' . DS . 'data' . DS . 'default' . DS . $fileName, $out);

            // init
            $fileName = 'init.php';
            $out =<<<EOD
<?php
\$this->Plugin->initDb('plugin', '{$plugin}');

EOD;

            $this->createFile($this->path . $plugin . DS . 'Config' . DS . $fileName, $out);
            
            // routes
            $fileName = 'routes.php';
            $out =<<<EOD
<?php
Router::connect('/{$_plugin}', array('plugin' => '{$_plugin}', 'controller' => '{$_plugin}', 'action' => 'index'));

EOD;

            $this->createFile($this->path . $plugin . DS . 'Config' . DS . $fileName, $out);
            
            // AppController
            $fileName = $plugin . 'Controller.php';
            $out =<<<EOD
<?php
class {$plugin}Controller extends BcPluginAppController {
	public \$uses = array('{$plugin}.{$plugin}Config');
	public function index() {
		\$datas = \$this->{$plugin}Config->findExpanded();
		\$this->set('message', \$datas['message']);
	}
}

EOD;

            $this->createFile($this->path . $plugin . DS . 'Controller' . DS . $fileName, $out);
            
            // ConfigsController
            $fileName = $plugin . 'ConfigsController.php';
            $out =<<<EOD
<?php
App::uses('BcPluginAppController', 'Controller');
class {$plugin}ConfigsController extends BcPluginAppController {
	public \$components = array('Cookie', 'BcAuth', 'BcAuthConfigure');
	public function admin_index() {
		\$this->pageTitle = '{$plugin} Title';
		
		if(\$this->request->data) {
			if(\$this->{$plugin}Config->saveKeyValue(\$this->request->data)) {
				\$this->setMessage('保存しました。');
			} else {
				\$this->setMessage('保存に失敗しました。', true);
			}
			\$this->redirect('index');
		}
		\$datas = \$this->{$plugin}Config->findExpanded();
		\$this->data = array('{$plugin}Config' => \$datas);
		\$this->render('form');
	}
}
EOD;

            $this->createFile($this->path . $plugin . DS . 'Controller' . DS . $fileName, $out);
            
            // ConfigModel
            $fileName = $plugin . 'Config.php';
            $out =<<<EOD
<?php
App::uses('BcPluginAppModel', 'Model');
class {$plugin}Config extends BcPluginAppModel {
	
}
EOD;

            $this->createFile($this->path . $plugin . DS . 'Model' . DS . $fileName, $out);
            
            // View
            $fileName = 'index.php';
            $out =<<<EOD
<?php
echo \$message;
EOD;

            $this->createFile($this->path . $plugin . DS . 'View' . DS . $plugin . DS . $fileName, $out);
            
            // Config View
            $fileName = 'form.php';
            $out =<<<EOD
<?php
\$url = fullUrl('/{$_plugin}');
?>

<?php echo \$this->BcForm->create('{$plugin}Config') ?>
<?php echo \$this->BcForm->label('{$plugin}Config.message', 'メッセージ') ?>　
<?php echo \$this->BcForm->input('{$plugin}Config.message', array('type' => 'text', 'size' => 60)) ?>
<div class="submit">
<?php echo \$this->BcForm->submit('保存', array('class' => 'button')) ?>
</div>

<p>表示確認：<?php \$this->BcBaser->link(\$url, \$url) ?></p>

EOD;

            $this->createFile($this->path . $plugin . DS . 'View' . DS . $plugin.'Configs' . DS . 'admin' . DS . $fileName, $out);
            
            // VERSION
            $fileName = 'VERSION.txt';
            $out =<<<EOD
1.0.0
EOD;

            $this->createFile($this->path . $plugin . DS . $fileName, $out);
            
            // config
            $fileName = 'config.php';
            $out =<<<EOD
<?php
\$title = 'プラグイン名';
\$adminLink = '/admin/{$_plugin}/{$_plugin}_configs/index';
\$author = '';
\$url = '';
\$description = 'プラグインの説明';

EOD;

            $this->createFile($this->path . $plugin . DS . $fileName, $out);
            
            $this->hr();
            $this->out(__d('cake_console', '<success>Created:</success> %s in %s', $plugin, $this->path . $plugin), 2);
        }

        return true;
    }

}
