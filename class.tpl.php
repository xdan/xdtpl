<?php
/**
 * tpl - templater
 * 
 * @author Chupurnov Valeriy http://xdan.ru
 * @website http://xdan.ru
 * @version 1.0.5
 */
defined('ROOT') or define('ROOT',dirname(__FILE__).'/'); // if not defined
class tpl{
  	private $vars = array();
	private $tpldir = 'tpl/';
	private $content_type = '';
	private $skin_path 	= false;
	/**
	 * setSkin - set skin path
	 *
	 * @param $name String - the path to the folder with alternate main template file
	 */
	function setSkin( $name ){
		$skin_path = ROOT.$this->tpldir.'skins/'.$name;
		if( file_exists($skin_path) and is_dir($skin_path) ){
			$this->skin_path = realpath($skin_path).'/';
		}else $this->skin_path = false;
	}
	
	public function __get( $name ){
		if( isset($this->vars[$name]) )
			return $this->vars[$name];
	}
	public function __set( $name,$value ){
		$this->vars[$name] = $value;
	}
	private function safe( $file ){
		$tpl_file = ROOT.$this->tpldir.$file.'.tpl';
		$alter_file = $this->skin_path?$this->skin_path.$file.'.tpl':false;
		if( $alter_file and file_exists($alter_file) ){
			return file_get_contents($alter_file);
		}else if( file_exists($tpl_file) ){
			return file_get_contents($tpl_file);
		}else{ 
			//console( 'Файл '.$file.' не найден ',__FILE__,__CLASS__,__LINE__ );
			return '';
		}
	}
	public function exec( $code ) {
		extract($this->vars);
		eval('?>'.$code );
	}
	
	public function assign( $var, $val='' ) {
		if( is_scalar($var) )
			$this->vars[$var] = $val;
		else if( is_array($var) )
			$this->vars = array_merge($this->vars,$var);
	}
	public function parse( $file,$vars = array() ) {
		ob_start();
		$this->show( $file,$vars );
		return ob_get_clean();
	}
	public function read( $file ){
		return $this->safe($file);
	}
	
	public function show( $file,$vars = array() ) {
		$this->assign($vars);
		$this->exec( $this->safe($file) );
	}
	function setContentType( $type ){
		if( in_array(strtolower($type),array('page','json','html')) )
			$this->content_type = strtolower($type);
	}
	function getContentType( ){
		return $this->content_type;
	}
	
	/**
	 * all2OneFile - parse all in-line script(and script file), and save all in one .js file for fast download page
	 *
	 * @param 	string 	$text html source
	 * @param 	mixed 	$file_except array excluded files? witch no include in one file
	 * @param 	string 	$full_pth path to dir where onefile be save
	 * @param 	string 	$script_pth url path for onefile
	 * @return 	string	Result source html, without all scripts, with one file  <script type="text/javascript" src="tmp/cache/f8b087b64b908a4c91d531c9921edb90.js"></script>
	 * @example	<script noonefile></script> no include in one file
	 */
	function all2OneFile( $text,$file_except = array(),$full_pth = 'tmp/cache/',$script_pth = 'tmp/cache/' ){
		if( preg_match_all( '#<script([^>]*)>(.*)</script>#Uis',$text,$slist ) ){
			$buf='';
			foreach($slist[1] as $i => $srcipt){
				if( ( preg_match('#src=("|\')([^"\']+)("|\')#',$srcipt,$list) and in_array( $list[2],$file_except ) ) or preg_match('#noonefile#',$slist[0][$i]) ){
					unset($slist[0][$i]);
					unset($slist[1][$i]);
					unset($slist[2][$i]);
				}
			}
			$file_name = md5(implode('',$slist[0]));
			if( !file_exists( $full_pth.$file_name.'.js' ) and file_exists($full_pth) and is_writable($full_pth) ){
				foreach( $slist[1] as $i=>$srcipt ){
					$filejs = ''; $src = '';
					if( preg_match('#src=("|\')([^"\']+)("|\')#',$srcipt,$list) ){
						$src = $list[2];
						if( preg_match('#^http:?\/\/#i',$src) ){
							$filejs = "//$src\n".file_get_contents($src);
						}else if( preg_match('#^\/\/#i',$src) ){
							$filejs = "//$src\n".file_get_contents('http:'.$src);
						}else{
							$file = preg_replace(array('#[\\\/]+#','#(\?.*)$#U'),array('/',''),ROOT.$src);
							if( is_readable( $file ) != false ){
								$filejs = "//$src\n".file_get_contents( $file );
							}else $filejs ='';
						}
					}else{
						$filejs = $slist[2][$i];
					}
					$buf.=$filejs."\n";
				}
				if( $buf!='' ){
					file_put_contents($full_pth.$file_name.'.js',$buf);
				}
			}
			if( file_exists( $full_pth.$file_name.'.js' ) ){
				$text = str_replace($slist[0][count($slist[0])-1],'<script type="text/javascript" src="'.$script_pth.$file_name.'.js"></script>',$text);
				$text = str_replace($slist[0],'',$text);
			}
		}
		return $text;
	}
       /**
        * Generate select tag
        * 
        * @param $list array [value=>text,...]
        * 
        * @return html
	*/
	function dropDownlist( $name,$value=false,$list = array(),$htmlOptions = array() ){
		$tag = '<select ';
		$htmlOptions['name'] = $name;
		$htmlOptions['id'] = preg_replace('#[^0-9a-zA-Z_]#','_',$name);
		foreach($htmlOptions as $key=>$val){
			$tag.=' '.$key.'="'.$val.'" ';
		}
		$tag.='>';
		foreach($list as $key=>$val){
			$tag.='<option '.(($value==$key or (is_array($value) and in_array($key,$value)))?'selected':'').' value="'.$key.'">'.$val.'</option>';
		}
		return $tag.'</select>';
	}
}
