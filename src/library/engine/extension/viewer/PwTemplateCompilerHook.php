<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * hook标签解析
 * 
 * 示例：
 * <code>
 * class MyClass {
 * public function plus($a, $b) {
 * echo $a + $b;
 * }
 * 
 * public static function myStatic() {
 * echo 'static';
 * }
 * }
 * $myclass = new MyClass();
 * 1、调用类中的方法可使用<hook class="$myClass" method="plus" args = "array(1,2)" alias='alias'/>
 * 2、调用类静态方法可使用<hook class="MyClass" method="mystatic" args = "array()" alias='alias'/>
 * 或<hook method="myClass::mystatic" args="array()" alias='alias'/>
 * 3、调用模板中的hook： <hook name="hookName" method="runDo" args = "array()" alias='alias'/> //runDo方法时默认调用的方法
 * 4、调用全局function可使用<hook method="func" args="array()" alias='alias'/>
 * </code>
 * 
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwTemplateCompilerHook.php 22627 2012-12-26 03:54:26Z jieyin $
 * @package wekit
 * @subpackage engine.extension.viewer
 */
class PwTemplateCompilerHook extends AbstractWindTemplateCompiler {
	
	/**
	 * 调用的类名
	 */
	protected $class;
	
	/**
	 * 调用的方法名
	 */
	protected $method;
	
	/**
	 * @var string|array
	 */
	protected $args;
	
	/**
	 * 该钩子下所有钩子片段集中缓存的位置
	 * @var string
	 */
	protected $alias = '';
	
	/**
	 * 钩子名称
	 *
	 * @var string
	 */
	protected $name = 'hook';

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$content = array();
		$content[] = '<?php';
		if (Wekit::load('APPS:appcenter.service.srv.PwDebugApplication')->inDevMode2()) {
			$_content = $this->_devHook();
			$content[] = 'echo \'' . WindSecurity::escapeHTML($_content) . '\';';
		}
		if (!$this->args) {
			$this->args = '';
		} else {
			$this->args = preg_replace(array('/\s*array\s*\(\s*/i', '/\s*\)\s*$/i'), '', $this->args);
		}
		$this->method = $this->method ? $this->method : 'runDo';
		if ($this->class) {
			$this->args = "'" . ltrim(strstr($this->name, '.'), '.') . "'" . ($this->args ? "," . $this->args : '');
			$callback = 'array(' . $this->class . ', "' . $this->method . '")';
		} elseif ($this->name) {
			$callback = 'array(PwSimpleHook::getInstance("' . $this->name . '"), "' . $this->method . '")';
		} else {
			$callback = '"' . $this->method . '"';
		}
		$this->args = 'array(' . $this->args . ')';
		$this->alias = trim($this->alias);
		$content[] = 'PwHook::display(' . $callback . ', ' . $this->args . ', "' . $this->alias . '", $__viewer);';
		$content[] = '?>';
		return implode("\r\n", $content);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::preCompile()
	 */
	public function preCompile() {
		$this->class = $this->method = $this->args = $this->name = '';
	}

	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('class', 'method', 'args', 'name', 'alias');
	}
	
	private function _devHook() {
		$_simple = !(in_array(substr($this->name, 0, 2), array('c_', 'm_')) || '(' === $this->name[0]);
		$_content = '';
		if ($_simple) {
			$_content = '<s_' . $this->name . '>';
		} else {
			list($_name, $_method) = explode('.', $this->name);
			list($hook_name) = explode('|', $_name);
			$hook = Wekit::load('hook.PwHooks')->fetchByName($hook_name);
			list(, , $interface) = explode("\r\n", $hook['document']);
			$interface = Wind::import($interface);
			$_content = '<' . $_name . ' ' . $interface . '.' . $_method . '>';
		}
		return $_content;
	}

}

?>