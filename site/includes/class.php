<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- PDF Generate
require_once dirname(__FILE__).'/plugin/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;

//-- Site
class zulu {

	public $SQL_table_log = 'log';

	function __construct($config) {

		$this->template = new stdClass();
		$this->config = new stdClass();
		$this->tpl = new stdClass();
		$this->defaults = new stdClass();
		$this->nav = new stdClass();

        $this->nav->menu = [];

		$this->template->head = '';
		$this->template->body = NULL;

		$this->config->session = session_id();
		$this->config->ip = $_SERVER['REMOTE_ADDR'];
		$this->config->timestamp = time();
		$this->config->tomorrow = 86399;
		$this->config->period_array = ['future'=>"All Upcoming",'today'=>"Today",'yesterday'=>"Yesterday",'last_7d'=>"Last 7 Days",'last_30d'=>"Last 30 Days",'week'=>"This Week",'week_last'=>"Last Week",'month'=>"This Month",'month_last'=>"Last Month",'custom'=>"Custom Period"];
		$this->config->page_max_page = MAX_per_page;
		$this->config->page_max = MAX_page_index;

		$this->defaults->currency_symbol = '$';
		$this->tpl->jquery = array();

		foreach($config as $data=>$values) {
			$this->config->{$data} = $values;
		}

		//-- Sort table listener
		$this->table_sort_listener();
	}
	function domain_clean($str,$config=[]) {
		if($config['tld']) {
			$tld = strrchr ( $str, "." );
			return substr ( $tld, 1 );
		} else {
			return str_replace(['http://','https://','www.'],'',$str);
		}
	}
	function path_clean($path) {
		return preg_replace('#/+#','/',$path);
	}
	function compile($join,$array) {
		foreach($array as $val) {
			if(trim($val)!=NULL) {
				$out .= ($i>0?$join:NULL).$val;
				$i++;
			}
		}
		return $out;
	}
	function in_array_r($needle, $haystack, $strict = false) {
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}
	function html_check($bool) {
		if($bool) {
			$html = "<span class=\"opt opt-success\"><span class=\"fas fa-check\"></span> Yes</span>";
		} else {
			$html = "<span class=\"opt opt-danger\"><span class=\"fas fa-times\"></span> No</span>";
		}
		return $html;
	}
	static function time_fancy($time,$config=[]) {
		$periods = array(array("second","sec"),array("minute","min"),array("hour","hr"), array("day","day"), array("week","wk"), array("month","mn"), array("year","yr"), array("decade","dec"));
		$lengths = array("60","60","24","7","4.35","12","10");

		$field = ($config['short']?1:0);
		$now = time();
		$difference = $now - $time;
		$tense = "ago";

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
		if($difference != 1) {
			$periods[$j][$field].= "s";
		}
		if($time<($now-604800)) { //if date smaller than 1 week
			$output = date("d/m/Y",$time);
		} else {
			$output = $difference." ".$periods[$j][$field]." ago";
		}
		$field = ($config['short']?'short':'long');

	   return $output;
	}
	static function thumb($src,$param,$config=[]) {
        require_once(MAIN_path.'includes/plugin/thumb/phpThumb.config.php');
        return phpThumbURL('src=../../../'.$src.'&'.$param, MAIN_rel.'includes/plugin/thumb/phpThumb.php');
	}
	function esc($str) {
		global $db;
		return $db->escape_string($str);
	}
	static function slug($text) {
		$output = [];
		if(!is_array($text)) {
			$slugs[] = $text;
		} else {
			$slugs = $text;
		}
		$sea = array(" ","%20","-","?","/","+");
		$sea2 = array("!","*","&","%","#","@","(",")",",",".",":",";","'","\"","°","\\");
		foreach($slugs as $text) {
			$val = str_replace($sea,"-",$text);
			$val = str_replace($sea2,"",$val);
			$val = str_replace("---","-",$val);
			$val = str_replace("--","-",$val);
			$output[] = strtolower($val);
		}

		return implode('/',$output);
	}
	static function date($ts,$format=NULL) {
		if($format==NULL) { $format = 'd/m/Y h:ia'; }
		return date($format,(float)$ts);
	}
	static function dateEncode($val,$time=false) {
		if($val == NULL) {
			return 0;
		}
		$timesplit = array(0,0);
		if($time) {
			$tsplit = explode(" ",$val);
			$val = $tsplit[0];
			$timesplit = (isset($tsplit[1])?explode(":",$tsplit[1]):array(0,0));
		}
		$date_split = explode('/', $val);
		if(count($date_split) < 3) {
			$parsed = strtotime($val);
			return ($parsed ? $parsed : 0);
		}
		list($day, $month, $year) = $date_split;
		$out = mktime((int)$timesplit[0], (int)$timesplit[1], 0, (int)$month, (int)$day, (int)$year);
		return $out;
	}

	static function dateDecode($val,$format='d/m/Y') {
		$out = date($format,$val);
		return $out;
	}
	static function time_decode($h,$m) {
		$mins = ($h*60);
		$mins += $m;
		return $mins;
	}
	static function time_encode($time) {
		settype($time, 'integer');
		if ($time < 1) {
			return;
		}
		$arr['h'] = floor($time / 60);
		$arr['m'] = sprintf("%02d",($time % 60));
		return $arr;
	}
	static function dollar($val,$nice=false) {
		return (!$nice?sprintf("%.2f",$val):number_format(sprintf("%.2f",$val),2));
	}
	static function shorten($body,$length) {
		$len = strlen($body);
		if($len > $length) {
			$con2 = strip_tags($body);
			$sS = str_split($con2, $length);
			$sS[0] .= "...";
			$dat = $sS[0];
		} else {
			$dat = $body;
		}
		return $dat;
	}
	static function time_scale($period,$tense="si") {
		switch($period) {
			case 'd':
			$txt['si'] = "Day";
			$txt['pl'] = "Daily";
			break;
			case 'w':
			$txt['si'] = "Week";
			$txt['pl'] = "Weekly";
			break;
			case 'm':
			$txt['si'] = "Month";
			$txt['pl'] = "Monthly";
			break;
			case 'y':
			$txt['si'] = "Year";
			$txt['pl'] = "Yearly";
			break;
		}
		return $txt[$tense];
	}

	//Objects HTML
	function object_link($obj,$id) {
		global $zulu;

		switch ($obj) {
			case 'product':
				$link = $zulu->link_page('product',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'quote':
				$link = $zulu->link_page('quote',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'bill':
				$link = $zulu->link_page('bill',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'project':
				$link = $zulu->link_page('project',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'task':
				$link = $zulu->link_page('task',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'renew':
				$link = $zulu->link_page('renew',array('query'=>array('id'=>$id,'Action'=>'edit')));
				break;
			case 'ticket':
				$link = $zulu->link_page('book',array('query'=>array('Solo'=>$id,'Action'=>'ticket','View'=>1)));
				break;
			case 'sale':
				$link = $zulu->link_page('sale',array('query'=>array('Action'=>'edit','Method'=>'View','id'=>$id)));
				break;
			case 'sale_line':
				$link = $zulu->link_page('sale',array('query'=>array('Action'=>'edit','Method'=>'View','sale_line_id'=>$id)));
				break;
			case 'membership':
				$link = $zulu->link_page('renew',array('query'=>array('Action'=>'edit','id'=>$id)));
				break;
			case 'sche_book':
				$link = $zulu->link_page('schedule',array('query'=>array('Action'=>'book_edit','id'=>$id)));
				break;
			case 'porder':
				$link = $zulu->link_page('porder',array('query'=>array('Action'=>'edit','id'=>$id)));
				break;
			case 'production_assy':
				$link = $zulu->link_page('production',array('query'=>array('Action'=>'assembly_edit','id'=>$id)));
				break;
		}
		return $link;
	}
	function object_title($str) {
		switch($str) {
			case 'job':
			case 'project':
				global $class_project;
				$val = $class_project->name();
				break;
			case 'sale':
				global $class_sale;
				$val = $class_sale->name();
				break;
			case 'sorder':
				global $class_sorder;
				$val = $class_sorder->name();
				break;
			case 'porder':
				global $class_porder;
				$val = $class_porder->name();
				break;
			case 'sche_book':
				$val = "Schedule Booking";
				break;
			default:
				$val = ucfirst(str_replace('_',' ',$str));
				break;
		}
		return $val;
	}
	static function leadingZero($val) {
		if($val<10 && $val>0) {
			$val = "0".$val;
		} elseif($val==NULL||$val==0) {
			$val = "00";
		}
		return $val;
	}
	function link_page($frame,$config=array(),$section=MASTER_section) {
		$query = "";
		$reserved = ['Page'];
		if(isset($config['self'])&&$config['self']) {
			foreach($_GET as $key=>$val) {
				if(!isset($config['query'][$key])&&!in_array($key,$reserved)) {
					if(is_array($val)) {
						foreach($val as $skey=>$sval) {
							if(!isset($config['query'][$key][$skey])&&!in_array($skey,$reserved)) {
								$config['query'][$key][$skey] = $sval;
								$txtkey = $key.'['.$skey.']';
								unset($config['query'][$txtkey]);
							}
						}
					} else {
						$config['query'][$key] = $val;
					}
				}
			}
		}
		if(isset($config['filter'])&&count($config['filter'])>0) {
			foreach($config['filter'] as $queryvar) {
				if(is_array($queryvar)) {
					foreach($queryvar as $qvs) {
						unset($config['query'][$queryvar][$qvs]);
					}
				} else {
					unset($config['query'][$queryvar]);
				}
			}
		}
		if(isset($config['query']) && count($config['query'])>0) {
			$query = "&".http_build_query($config['query']);
		}
		$url = MAIN_rel.$this->config->SECTION_inclusion[$section]['path']."index.php?Page=".$frame.$query;
		if(isset($config['fe'])&&$config['fe']) {
			$front_path = $frame;
			if(is_bool($frame)) {
					$front_path = strtok($_SERVER['REQUEST_URI'],'?');
			}
			$url = $front_path."?".$query;
		}
		return $url;
	}
	function front_link($path,$config=[]) {
		$newconfig = $config;
		$newconfig['fe'] = true;
		$path = $this->link_page($path,$newconfig);
		return $path;
	}
	function button_render($bt_array,$config=[]) {

		$btn_size = 'xs';
		$root_class_append = [];
		if(isset($config['class']) && count($config['class'])>0) {
			$root_class_append[] = implode(" ",$config['class']);
		}
		if(isset($config['size'])) {
			$btn_size = $config['size'];
		}
		foreach($bt_array as $bt) {
			$has_drop = false;
			$data_attr = [];
			$data_append = [];
			$class_append = $root_class_append;

			if(isset($bt['popup']) && $bt['popup']) {
				$bt['class_append'][] = 'popup';
				$bt['data']['fancybox-type'] = 'iframe';
			}

			if(!isset($bt['class_append'])) {
				$bt['class_append'] = [];
			}
			if(isset($bt['data']) && count($bt['data'])>0) {
				foreach($bt['data'] as $dkey=>$dval) {
					$data_append[$dkey] = $dval;
				}
			}
			if(isset($bt['option']) && count($bt['option'])>0) {
				$has_drop = true;
				$root_clicks = ($bt['link']!='#'&&trim($bt['link'])!=NULL?true:false);
				if(!$root_clicks) {
					$class_append[] = 'dropdown-toggle';
					$data_append['toggle'] = 'dropdown';
				}
			}
			if(isset($bt['onclick'])) {
				$data_attr[] = "onclick=\"".$bt['onclick']."\"";
			}
			foreach($data_append as $dkey=>$dval) {
				$data_attr[] = "data-".$dkey."=\"".$dval."\"";
			}
            if(!isset($bt['target'])) {
				$bt['target']="";
			}
            if(!isset($config['ovr_btn'])) {
				$config['ovr_btn']=false;
			}


			$button_html = "<a target=\"".$bt['target']."\" href=\"".$bt['link']."\" ".implode(' ',$data_attr)." class=\"".(!$config['ovr_btn']?"btn btn-".$bt['class']:NULL)." ".implode(" ",$bt['class_append']+$class_append)." btn-".$btn_size."\"><i class=\"fas fa-".$bt['icon']."\"></i> ".$bt['label'].($has_drop&&!$root_clicks?" <span class=\"caret\"></span>":NULL)."</a>";

			if(isset($bt['option']) &&  count($bt['option'])>0) {
				$dropdown_implode = [];
				$dropdown_buttons = $this->button_render($bt['option'],['return'=>'array','ovr_btn'=>true]);
				foreach($dropdown_buttons as $db) {
					$dropdown_implode[] = "<li>".$db."</li>";
				}
				$button_html = "
				<div class=\"btn-group\">
					".$button_html."
					".($root_clicks?"
					<button class=\"btn ".(!$config['ovr_btn']?"btn btn-".$bt['class']:NULL)." btn-xs dropdown-toggle\" data-toggle=\"dropdown\">
						<span class=\"caret\"></span>
					</button>
					":NULL)."
					<ul class=\"dropdown-menu\">
						".implode(PHP_EOL,$dropdown_implode)."
					</ul>
				</div>";
			}
			$button[] = $button_html;
			unset($dropdown_implode,$dropdown_buttons,$data_append,$class_append);
		}
		if(isset($config['return']) && $config['return']=='array') {
			return $button;
		} else {
			return implode(" ",$button);
		}
	}
	function table_sort_listener() {
		global $db;
		if(isset($_GET['table_s'])) {
			$table_id = $_GET['table_s']['id'];
			$_SESSION['zl_table']['sort'][$table_id]['col'] = $db->escape_string($_GET['table_s']['col']);
			$_SESSION['zl_table']['sort'][$table_id]['sort'] = $db->escape_string($_GET['table_s']['sort']);
		}
	}
	function table_sort_query($table_id,$default=NULL) {
		if(isset($_SESSION['zl_table']['sort'][$table_id])&&$_SESSION['zl_table']['sort'][$table_id]['col']!=NULL) {
			if(strstr($_SESSION['zl_table']['sort'][$table_id]['col'],',')) {
				$expl = explode(',',$_SESSION['zl_table']['sort'][$table_id]['col']);
				foreach($expl as $e) {
					$qry[] = $e.' '.($_SESSION['zl_table']['sort'][$table_id]['sort']!=NULL?$_SESSION['zl_table']['sort'][$table_id]['sort']:'ASC');
				}
				return implode(", ",$qry);
			} else {
				return $_SESSION['zl_table']['sort'][$table_id]['col'].' '.($_SESSION['zl_table']['sort'][$table_id]['sort']!=NULL?$_SESSION['zl_table']['sort'][$table_id]['sort']:'ASC');
			}
		} else {
			return ($default==NULL?'id ASC':$default);
		}
	}
	function table_render($data,$mini=0,$config=array()) {
		global $zulu;

		$html = ['header'=>"",'body'=>""];
		$table_id = (!isset($config['html_id'])||$config['html_id']==NULL?"dataTable-".rand(1,9999):$config['html_id']);
		foreach($data as $row) {
			$tr_parameter = [];
			if(isset($row['header']) && $row['header']) {
				//header row
				$html['header'] .= "<tr ".implode(" ",$tr_parameter)." class=\"\">\n";
				foreach($row['content'] as $column) {
					$td_parameter = [];
					if(is_array($column)) {
						$content = $column[0];
						$extra_class = implode(" ",$column[1]['class']);
						$extra_style = implode(" ",$column[1]['style']);
						$td_parameter[] = (isset($column[1]['valign'])&&$column[1]['valign']!=NULL?"valign=\"".$column[1]['valign']."\"":NULL);
						$td_parameter[] = (isset($column[1]['colspan'])&&$column[1]['colspan']>0?"colspan=\"".$column[1]['colspan']."\"":NULL);
						$td_parameter[] = (isset($column[1]['rowspan'])&&$column[1]['rowspan']>0?"rowspan=\"".$column[1]['rowspan']."\"":NULL);
						if(isset($column[1]['sort'])) {
							$controls[] = "<a title=\"Ascending\" href=\"".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['table_s'=>['id'=>$config['html_id'],'sort'=>'ASC','col'=>$column[1]['sort']['db_column']]]])."\"><i class=\"fas fa-chevron-down\"></i></a>";
							$controls[] = "<a title=\"Descending\" href=\"".$zulu->link_page(PAGE_file,['self'=>true,'query'=>['table_s'=>['id'=>$config['html_id'],'sort'=>'DESC','col'=>$column[1]['sort']['db_column']]]])."\"><i class=\"fas fa-chevron-up\"></i></a>";
						}
					} else {
						$content = $column;
					}
					$html['header'] .= "<th ".implode(" ",$td_parameter)." class=\"{$extra_class}\" style=\"{$extra_style}\">".$content."<span class=\"controls\">".implode(' ',$controls)."</span></th>\n";
					unset($extra_class,$controls,$extra_style);
				}
				$html['header'] .= "</tr>";
			} else {
				//normal row
				$data_item = [];
				if(isset($row['data']) && count($row['data'])>0) {
					foreach($row['data'] as $dkey=>$dval) {
						$data_item[] = "data-".$dkey."=\"".$dval."\"";
					}
				}

				$html['body'] .= "<tr ".implode(" ",$tr_parameter)." ".(isset($row['class'])?"class=\"".$row['class']."\" ":NULL)."".implode(" ",$data_item).">\n";
				foreach($row['content'] as $column) {
					$td_parameter = [];
					if(is_array($column)) {
						$content = $column[0];
						$extra_class = (!empty($column[1]['class'])?(is_array($column[1]['class'])?implode(" ",$column[1]['class']):$column[1]['class']):NULL);
						$extra_style = implode(" ",$column[1]['style']);
						$td_parameter[] = (isset($column[1]['valign'])&&$column[1]['valign']!=NULL?"valign=\"".$column[1]['valign']."\"":NULL);
						$td_parameter[] = (isset($column[1]['colspan'])&&$column[1]['colspan']>0?"colspan=\"".$column[1]['colspan']."\"":NULL);
						$td_parameter[] = (isset($column[1]['rowspan'])&&$column[1]['rowspan']>0?"rowspan=\"".$column[1]['rowspan']."\"":NULL);
					} else {
						$content = $column;
					}
					if(trim($content)!='~') {
						$html['body'] .= "<td ".implode(" ",$td_parameter)." class=\"{$extra_class}\" style=\"{$extra_style}\">".$content."</td>\n";
					}
					unset($extra_class,$extra_style);
				}
				$html['body'] .= "</tr>";
			}
		}
		$master_class = [];
		if(isset($config['class']) && count($config['class'])>0) {
			$master_class[] = $config['class'];
		}
		$tbody_data = "";
		if(isset($config['tbody']['id']) && $config['tbody']['id']!=NULL) {
			$tbody_data = "id=\"".$config['tbody']['id']."\"";
		}
		$thead_data = "";
		if(isset($config['thead']['id']) && $config['thead']['id']!=NULL) {
			$thead_data = "id=\"".$config['thead']['id']."\"";
		}
		$html = "<table class=\"table table-striped table-bordered table-hover ".implode(' ',$master_class)."\" id=\"".$table_id."\">
			".($html['header']!=NULL?"<thead {$thead_data}>{$html['header']}</thead>":NULL)."
			".($html['body']!=NULL?"<tbody {$tbody_data}>{$html['body']}</tbody>":NULL)."
		</table>";
		/*$this->tpl->jquery[] = "
        $('#".$table_id."').DataTable({
                responsive: true
        });";*/

		$table_config = (isset($config['data_table'])?$config['data_table']:[]);
		if(isset($table_config['sort'])) {
			foreach($table_config['sort'] as $sort) {
				$sort = explode(',',$sort);
				$table_sort[] = "[ ".$sort[0].", \"".$sort[1]."\" ]";
			}
		}
		if($table_config==false&&isset($config['data_table'])) { } else {
			$this->template->js_code[] = "
			$('#".$table_id."').DataTable({
					\"responsive\": true,
					".(!isset($table_config['pages'])||$table_config['pages']==false?"\"paging\": false":"\"lengthMenu\": [[25, 50, 100, -1], [25, 50, 100, \"All\"]]").",
					".(!isset($table_config['sort'])||$table_config['sort']==false?"\"ordering\": false":"\"order\": [".(count($table_sort)>0?implode(',',$table_sort):"[ 0,\"desc\"]")."]").",
					".(isset($table_config['search'])&&$table_config['search']==false?"\"bFilter\": false":"\"bFilter\": true").",
					\"stateSave\": true,
			});";
		}
		return $html;
	}
	static function table_data($table,$id=0,$configuration=NULL,$resource=NULL) {
		global $MAINDB_cxn, $db, $table_data_count;

		if($resource != NULL) {
			$mysqli = $resource->mysqli;
		} else {
			$mysqli = $db->mysqli;
		}

		$test = false;
		if(isset($configuration['test']) && $configuration['test']) {
			$test = true;
		}

		$primary_field = (isset($configuration['primary_field'])&&$configuration['primary_field']!=""?$configuration['primary_field']:"id");
		$default_sort = (isset($configuration['sort'])&&$configuration['sort']!=""?$configuration['sort']:$primary_field." ASC");
		if(isset($configuration['where']) && $configuration['where'] != NULL && !is_array($configuration['where'])) {
			$configuration['where'] = array($configuration['where']);
		}
		if(isset($configuration['field']) && $configuration['field'] != NULL && !is_array($configuration['field'])) {
			$configuration['field'] = array($configuration['field']);
		}
		$where_sql = (isset($configuration['where'])&&count($configuration['where'])>0?" AND ".implode(" AND ",$configuration['where']):NULL);
		$field_sql = (isset($configuration['field'])&&count($configuration['field'])>0?implode(",",$configuration['field']):NULL);
		$sql_limit = (isset($configuration['limit'])&&$configuration['limit']>0?$configuration['limit']:99999999);
		$sql_start = (isset($configuration['start'])&&$configuration['start']>0?$configuration['start']:0);
		$join_sql = (isset($configuration['join'])&&$configuration['join']!=NULL?(is_array($configuration['join'])?" JOIN ".implode(" JOIN ",$configuration['join']):" JOIN ".$configuration['join']):NULL);
		$join_sql .= (isset($configuration['join_left'])&&$configuration['join_left']!=NULL?(is_array($configuration['join_left'])?" LEFT JOIN ".implode(" LEFT JOIN ",$configuration['join_left']):" LEFT JOIN ".$configuration['join_left']):NULL);
		$group_sql = (isset($configuration['group'])&&$configuration['group']!=NULL?" GROUP BY ".$configuration['group']:NULL);
		$table_name = (strstr($table," ")?$table:"`".$table."`");

		if($id>0) { //certain row data
			$array = array();
			if($test) {
				echo "SELECT ".($field_sql!=NULL?$field_sql:"*")." FROM {$table_name}{$join_sql} WHERE {$primary_field} = '".$id."' {$where_sql}{$group_sql} ORDER BY {$default_sort} LIMIT 1\n";
			}

			$mysqli_result = $mysqli->query("SELECT ".($field_sql!=NULL?$field_sql:"*")." FROM {$table_name}{$join_sql} WHERE {$primary_field} = '".$id."' {$where_sql}{$group_sql} ORDER BY {$default_sort} LIMIT 1") or die($mysqli->error);

			$table_data_count = $mysqli_result->num_rows;
			$row = $mysqli_result->fetch_array((isset($configuration['assoc'])&&$configuration['assoc']?MYSQLI_ASSOC:MYSQLI_BOTH));

			if(count($row)>0&&$row!=NULL&&!empty($row)) {
				foreach($row as $key=>$value) {
					$array[$key] = $value;
				}
			} else {
				$array = array();
			}
		} else { //all rows
			$array = array();
			if($test) {
				echo "SELECT ".($field_sql!=NULL?$field_sql:"*")." FROM {$table_name}{$join_sql} WHERE 1 {$where_sql}{$group_sql} ORDER BY {$default_sort} LIMIT {$sql_start},{$sql_limit}\n";
			}

			$mysqli_result = $mysqli->query("SELECT ".($field_sql!=NULL?$field_sql:"*")." FROM {$table_name}{$join_sql} WHERE 1 {$where_sql}{$group_sql} ORDER BY {$default_sort} LIMIT {$sql_start},{$sql_limit}");

			$table_data_count = $mysqli_result->num_rows;
            if($table_data_count > 0) {
                while($row = $mysqli_result->fetch_assoc()) {
                    $array[] = $row;
                    if(isset($configuration['first'])&&$configuration['first']) {
                        $array = $row;
                        break;
                    }
                }
            }
		}
		return $array;
	}
	static function makeHT($str) {
		$val = zulu::slug($str);
		return $val;
	}
	static function serial($length=24,$numeric=0) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		if($numeric) {
			$chars = "0123456789";
		}

		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}

		return $str;
	}
	function fatal_error($title,$message) {
		echo "<h1>".$title."</h1>";
		echo "<p>".$message."</p>";
		exit;
	}
	function notification_set($error='',$error_class=1,$config=[]) {
		if($config['tag']!=NULL) {
			$tag = $config['tag'];
			$_SESSION['SH_ErrorA'][$tag] = $error;
			$_SESSION['SH_Error_ClassA'][$tag] = $error_class;
			$_SESSION['SH_Error_ConfigA'][$tag] = $config;
		} else {
			$_SESSION['SH_Error'] = $error;
			$_SESSION['SH_Error_Class'] = $error_class;
			$_SESSION['SH_Error_Config'] = $config;
		}
		return true;
	}
	function notification($error='', $error_class=1, $config=[]) {
		$alert_class = [];

        if(strlen($error)) {
            $alert_class[] = "alert-".($error_class==1?"success":"danger");

        } elseif(isset($config['tag']) && $config['tag']!=NULL) {
			$tag = $config['tag'];
			if(isset($_SESSION['SH_ErrorA'][$tag]) && strlen($_SESSION['SH_ErrorA'][$tag])) {
				$error = $_SESSION['SH_ErrorA'][$tag];
				$error_class = $_SESSION['SH_Error_ClassA'][$tag];
				$error_config = $_SESSION['SH_Error_ConfigA'][$tag];
				if($error_config['class']!=NULL) {
					$alert_class = $error_config['class'];
				} else {
					$alert_class[] = "alert-".($error_class==1?"success":"danger");
				}
				unset($_SESSION['SH_Error_ClassA'][$tag], $_SESSION['SH_ErrorA'][$tag]);
			}
		} else {
			if(isset($_SESSION['SH_Error']) && strlen($_SESSION['SH_Error'])) {
				$error = $_SESSION['SH_Error'];
				$error_class = $_SESSION['SH_Error_Class'];
				$error_config = $_SESSION['SH_Error_Config'];
				if($error_config['class']!=NULL) {
					$alert_class = $error_config['class'];
				} else {
					$alert_class[] = "alert-".($error_class==1?"success":"danger");
				}
				unset($_SESSION['SH_Error_Class'], $_SESSION['SH_Error']);
			}
		}

		if(!strlen($error)) {
			$html = "";
		} else {
			$html = "<div class=\"alert alert-dismissible ".implode(" ", $alert_class)."\">
                <a href='#' class='close' data-dismiss='alert' aria-label='Close'><span class='far fa-times'></span></a>
                {$error}
            </div>";
		}

		return $html;
	}

	function meta_remove($table,$id,$field,$value=NULL,$member=NULL) { //Remove Meta Data Tag
		global $db;
		if(!$db->query("DELETE FROM {$table}_meta WHERE identifier = '$id' AND field = '$field'".($value!=NULL?" AND value = '".$value."'":NULL).($member>0?" AND member = '".$member."'":NULL))) {
			return false;
		} else {
			return true;
		}
	}

	function meta_clear($table,$id) {
		global $db;
		if(!$db->query("DELETE FROM {$table}_meta WHERE identifier = '$id'")) {
			return false;
		} else {
			return true;
		}
	}

	function meta_check_update($table,$id,$fields) {
		if(count($_POST['meta'])>0) { //update meta if any
			foreach($_POST['meta'] as $mfield=>$mval) {
				$this->meta_update($table,$id,$mfield,$mval);
			}
		}
		return;
	}

	function meta_update($table,$id,$field,$value,$label=NULL,$member=0,$multi=false) { //Update Meta Data Tag
		global $db;

		$mysqli_result = $db->mysqli->query("SELECT id FROM {$table}_meta WHERE identifier = '$id' AND field = '$field'".($member>0?" AND member = '".$member."'":NULL)."".($multi?" AND value = '$value'":NULL)."");
		if($mysqli_result->num_rows > 0) {

			$query = "UPDATE {$table}_meta SET label = '{$object_title}', value = '".$value."' WHERE identifier = '$id' AND field = '$field'".($member>0?" AND member = '".$member."'":NULL).($multi?" AND value = '$value'":NULL);
		} else {

			$query = "INSERT INTO {$table}_meta (identifier,field,label,value".($member>0?",member":NULL).") VALUES ('{$id}','{$field}','{$label}','{$value}'".($member>0?",'{$member}'":NULL).")";
		}
		return ($db->query($query)?true:false);
	}

	function meta_array($array, $multi=false) {
		$new = [];
		foreach($array as $row) {
            if($multi) {
                if(isset($new[$row['field']]) && !is_array($new[$row['field']]) && $new[$row['field']] != $row['value']) {
                    $new[$row['field']] = [$new[$row['field']]];
                }
                if(isset($new[$row['field']]) && is_array($new[$row['field']])) {
                    if(is_array($new[$row['field']])) {
                        $new[$row['field']][] = (isset($row['value'])?$row['value']:NULL);
                    }
                } elseif(isset($row['field'])) {
                    $new[$row['field']] = (isset($row['value'])?$row['value']:NULL);
                }
            } elseif(isset($row['field'])) {
				$new[$row['field']] = (isset($row['value'])?$row['value']:NULL);
			}
		}
		return $new;
	}

	static function meta_value($table,$id,$field=NULL,$value=NULL,$member=0,$justfield=NULL) { //Meta Data
		global $db;

		$mysqli_result = $db->mysqli->query("SELECT * FROM {$table}_meta WHERE identifier = '".$id."'".($field!=NULL?" AND field = '$field'":NULL).($value!=NULL?" AND value = '$value'":NULL));
		$row_ITEM = $mysqli_result->fetch_array();
		if($field!=NULL) {
			if($mysqli_result->num_rows > 1) {
                $return[] = $row_ITEM;
                $return[$row_ITEM['field']] = $row_ITEM;
				while($row_ITEM = $mysqli_result->fetch_assoc()) {
					$return[] = $row_ITEM;
					if($return[$row_ITEM['field']] == NULL) {
						$return[$row_ITEM['field']] = $row_ITEM;
					} else if($return[$row_ITEM['field']]['id'] == NULL) {
						$return[$row_ITEM['field']][] = $row_ITEM;
					} else {
						$temp = $return[$row_ITEM['field']];
						$return[$row_ITEM['field']] = [$temp,$row_ITEM];
					}
				}
			} else {
				$return = $row_ITEM;

				if($justfield!=NULL) {
					$return = $return[$justfield];
				}
			}
		} else {
			if($mysqli_result->num_rows > 1) {
				$return[] = $row_ITEM;
				$return[$row_ITEM['field']] = $row_ITEM;
				while($row_ITEM = $mysqli_result->fetch_assoc()) {
					$return[] = $row_ITEM;
					if(!isset($return[$row_ITEM['field']]) || $return[$row_ITEM['field']] == NULL) {
						$return[$row_ITEM['field']] = $row_ITEM;
					} else if($return[$row_ITEM['field']]['id'] == NULL) {
						$return[$row_ITEM['field']][] = $row_ITEM;
					} else {
						$temp = $return[$row_ITEM['field']];
						$return[$row_ITEM['field']] = [$temp,$row_ITEM];
					}
				}
			} else {
				$return[] = $row_ITEM;
				$return[$row_ITEM['field']] = $row_ITEM;
			}
		}

		return $return;
	}

	//Meta Append to Array
	function meta_append($source,$config=array()) {
		if($config['field']!=NULL) {
			$field = $config['field'];
		} else {
			$field = 'value';
		}
		foreach($source as $key=>$val) {
			if($config['post']) {
				$_POST[$key] = $val[$field];
			} elseif($config['get']) {
				$_GET[$key] = $val[$field];
			} else {
				$destination[$key] = $val;
			}
		}
		if(count($destination)>0) {
			return $destination;
		} else {
			return true;
		}
	}

	//Meta Data
	function meta_implode($table,$id,$field) {
		global $db;

		$return = array();
		$mysqli_result = $db->mysqli->query("SELECT value FROM {$table}_meta WHERE identifier = '".$id."' AND field = '$field'");
		while($row_ITEM = $mysqli_result->fetch_assoc()) {
			$return[] = $row_ITEM['value'];
		}

		return $return;
	}

	//Fancy Time
	static function time_history($time,$config=[]) {
		$periods = array(array("second","sec"),array("minute","min"),array("hour","hr"), array("day","day"), array("week","wk"), array("month","mn"), array("year","yr"), array("decade","dec"));
		$lengths = array("60","60","24","7","4.35","12","10");
		$format_base = 'd/m/Y';

		if(isset($config['format']) && $config['format']!=NULL) {
			$format_base = $config['format'];
		}

		$field = (isset($config['short'])&&$config['short']?1:0);
		$now = time();
		$difference = $now - $time;
		$tense = "ago";

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
		if($difference != 1) {
			$periods[$j][$field].= "s";
		}
		if($time<($now-604800)) { //if date smaller than 1 week
			$output = date($format_base,$time);
		} else {
			$output = $difference." ".$periods[$j][$field]." ago";
		}
		$field = (isset($config['short'])&&$config['short']?'short':'long');

		if($time==0) {
			$output = "Never";
		}
		if($time>time()) {
			$output = date($format_base,$time);
		}

	   return $output;
	}

	//Mail Log Read
	function mail_log_read($serial) {
		global $db;

		$data = $this->table_data('email_log',0,['first'=>true,'where'=>["serial = '".$serial."'","serial != ''"]]);
		if($data['date_read']<=0) {
			$db->query("UPDATE email_log SET date_read = '".time()."' WHERE id = '".$data['id']."'");
			return true;
		} else {
			return false;
		}
	}

	//Mail Log
	function mail_log_data($config=[]) {
		global $class_user;

		$qry = ['where'=>['toggle=1','user_id='.$class_user->authorised->id],'sort'=>'id DESC'];

		if($config['id']>0) {
			$id = $config['id'];
		}
		if($config['id']>0) {
			$id = $config['id'];
		}
		if($config['serial']!=NULL) {
			$qry['where'][] = "serial = '".$config['serial']."'";
		}
		if($config['first']) {
			$qry['first'] = true;
		}
		if($config['token']!=NULL) {
			$qry['where'][] = "token = '".$config['token']."'";
		}
		$data = $this->table_data("email_log",$id,$qry);
		return $data;
	}

	//Mail Log
	function mail_log($data) {
		global $class_user, $db;
		$combine = ['toggle'=>$data['toggle'],'serial'=>$data['serial'],'user_id'=>($data['user_id']>0?$data['user_id']:$class_user->authorised->id),'token'=>$this->serial(16),'date_sent'=>time()]+$data;
		$query = "INSERT INTO email_log ".$db->build(2,array('toggle','serial','user_id','token','msg_to','msg_from','msg_subject','date_sent','object_id','object'),$combine);
		return ($db->query($query)?true:false);
	}

	//Send Mail
	function mail_send($to, $subject, $message, $attachment='', $tpl_override=false, $config=[]) {
		global $class_setting, $class_user;

		//-- Dev mode?
		if(defined('DEV_mode') && DEV_mode && defined('DEV_email')) {
			$to = DEV_email;
		}

		if(!$config['no_branding']) {
			$footer_EMAIL = "&copy;".date('Y')." ".MAIN_name.". All rights reserved. Email is confidential and should be destroyed if no longer required.";
		}
		if($config['user_id']>0) {
			$setting = $class_setting->setting_data(['user_id'=>$config['user_id']]);
		} else {
			$setting = $class_setting->data;
		}

		if($config['no_branding']||$config['client']) {
			$from_company = $setting['company'];
			$from_email = $setting['contact_email'];

			$tpl_header = "<div id='header'><img class=\"logo\" src=\"".MAIN_url."file/user/".$setting['user_id']."/".$setting['quote_logo']."\" alt='logo' /></div>";
		} else {
			$from_company = MAIL_name;
			$from_email = MAIL_email;
			$tpl_header = "<div id='header'><img class=\"logo\" src=\"".TPL_abs."images/logo-email.jpg\" alt=\"logo\" /></div>";
		}

        if(isset($setting['smtp_email']) && $setting['smtp_email']) {
            $from_email = $setting['smtp_email'];
        }
        if(isset($config['from_email']) && $config['from_email']) {
            $from_email = $config['from_email'];
        }
        if(isset($config['from_company']) && $config['from_company']) {
            $from_company = $config['from_company'];
        }

		//MSG
		$thread_serial = $this->serial(8);
		$msg_serial = $this->serial(8);
		$body = "<html>
            <head>
              <title>".$from_company." | Email</title>
                ".(!$tpl_override?"<link href=\"".TPL_abs."css/style.mail.css?renew=".rand()."\" type=\"text/css\" rel=\"stylesheet\" />":NULL)."
            </head>
            <body>
                ".(!$tpl_override?"
                <div id=\"container\">
                    ".$tpl_header."
                    <div id='subject'><h1>".$subject."</h1></div>
                    <div id='msgbody'>".$message."</div>
                    <div id='footer'><p><strong>Kind Regards</strong>,<br>".$from_company."</p></div>
                    <div id='copyright'>".$footer_EMAIL."</div>
                </div>":$message)."

                <img src=\"".MAIN_url."includes/cron/mail.php?Action=Mark&Serial={$msg_serial}\" width=\"1\" height=\"1\" />
            </body>
		</html>";

		$DEFAULT_mail_override = false;
		if($DEFAULT_mail_override) {
            $header_options = [
                'MIME-Version: 1.0',
                'Content-type: text/html;charset=iso-8859-1',
                'From: '.$from_company.' <'.$from_email.'>'
            ];
			$headers = implode('\n', $header_options);
			mail($to, $subject, $body, $headers);

		} else {
			$mail = new PHPMailer();
			$mail->CharSet = 'UTF-8';
            $to_log = [];

            $smtp_method = (isset($config['smtp_method']) ? $config['smtp_method'] : $setting['smtp_method']);

            if($smtp_method == 'smtp') {
                //-- use smtp method
                $smtp_host = (isset($config['smtp_host']) ? $config['smtp_host'] : $setting['smtp_host']);
                $smtp_encryption = (isset($config['smtp_encryption']) ? $config['smtp_encryption'] : $setting['smtp_encryption']);
                $smtp_port = (isset($config['smtp_port']) ? $config['smtp_port'] : $setting['smtp_port']);
                $smtp_username = (isset($config['smtp_username']) ? $config['smtp_username'] : $setting['smtp_username']);
                $smtp_password = (isset($config['smtp_password']) ? $config['smtp_password'] : ($setting['smtp_password'] ? $this->stringDecrypt($setting['smtp_password']) : ''));

                $mail->IsSMTP();
				$mail->Host       = $smtp_host;
				$mail->SMTPDebug  = false;

				if($smtp_username && $smtp_password) {
					$mail->SMTPAuth   = true;
					$mail->Username   = $smtp_username;
					$mail->Password   = $smtp_password;
				}
                if($smtp_port) {
                    $mail->Port = $smtp_port;
                } else {
                    $mail->Port = 25;
                }
                if($smtp_encryption == 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;;
                } elseif($smtp_encryption == 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }

            } else {
                //-- use default sendmail
                $mail->IsSendmail();
            }

            //-- attachments
			if($attachment) {
                if(!is_array($attachment)) {
                    $attachment = [$attachment];
                }
                if(count($attachment) > 0) {
                    foreach($attachment as $attach) {
                        $mail->AddAttachment($attach);
                    }
                }
            }

            //-- reply to
			if(isset($config['reply']) && $config['reply']) {
                $mail->AddReplyTo($config['reply']);
            } else {
				$mail->AddReplyTo($from_email, $from_company);
			}

            //-- send from
			$mail->SetFrom($from_email, $from_company);

            //-- bcc
			if(isset($config['bcc']) && count($config['bcc']) > 0) {
				foreach(explode(',', $config['bcc']) as $email) {
                    $email = trim($email);
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->AddBCC($email);
                        $to_log[] = $email;
                    }
				}
			}

            // send to
            if($to != null) {
				foreach(explode(',', $to) as $email) {
                    $email = trim($email);
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
					   $mail->AddAddress($email);
					   $to_log[] = $email;
                    }
				}
			}

            if(count($to_log) <= 0) {
                return false;
            }

            //-- cc
            if(isset($config['cc']) && count($config['cc']) > 0) {
				foreach(explode(',', $config['cc']) as $email) {
                    $email = trim($email);
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->AddCC($email);
                    }
				}
			}

			$mail->Subject = $subject;
			$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->MsgHTML($body);

            //-- send email
			if(!$mail->Send()) {
                if(defined('DEBUG_mode') && DEBUG_mode) {
                    echo "Mail Error: ".$mail->ErrorInfo;
                    exit;
                }
                return false;

			} else {
                //-- log the email details
				$mlog = [
                    'toggle'        =>  $config['toggle'],
                    'serial'        =>  $msg_serial,
                    'serial_thread' =>  $thread_serial,
                    'msg_subject'   =>  $subject,
                    'msg_to'        =>  implode(",", $to_log),
                    'msg_from'      =>  $from_email,
                    'object'        =>  $config['object'],
                    'object_id'     =>  $config['object_id']
                ];
				if($config['user_id'] > 0) {
					$mlog['user_id'] = $config['user_id'];
				} elseif($class_user->authorised->id > 0) {
					$mlog['user_id'] = $class_user->authorised->id;
				}
				$this->mail_log($mlog);

                return true;
			}
		}
	}
	function percent($fraction,$total) {
		if($total>0) {
			$percent = ($fraction/$total)*100;
			return ($percent>=10?$percent:number_format($percent,1));
		} else {
			return 0;
		}
	}

	//-- Log
	function log_create($type,$title) {
		$this->data->log = [];
		$this->data->log_full = [];
		$this->data->log_title = $title;
		$this->data->log_type = $type;
	}
	function log_add($msg) {
		$this->data->log[] = $msg;
	}
	function log_block($title) {
		$this->data->log_full[] = "<b>{$title}</b><br>".implode("<br>",$this->data->log);
	}
	function log_publish() {
		global $db;
		$email = "START ".date("d/m/Y h:ia")." | IP: ".$_SERVER['REMOTE_ADDR']. " | ITEMS: ".count($this->data->log);
		foreach($this->data->log_full as $msg) {
			$email = "<BR><BR>".$msg;
		}
		$vals = array("object"=>$this->data->log_type,"title"=>$this->data->log_title,"data"=>$email,"stat_add"=>time());
		foreach($vals as $key=>$val) {
			$fields[] = $key;
		}
		$db->query("INSERT INTO log ".$db->build(0,$fields,$vals));
		return;
	}
	private function log_new($data) {
		global $db,$zulu,$class_user,$DB_cxn;

		$data = ['user_id'=>$class_user->authorised->id,'stat_add'=>time()];
		$query = "INSERT INTO ".$this->SQL_table_log." ".$db->build(2,['user_id','stat_add'],$data);

		if($db->query($query)) {
			return ['id'=>$db->insert_id,'success'=>true];
		} else {
			return ['success'=>false];
		}
	}
	function log_edit($id,$config=array()) {
		global $db,$zulu;

		if($id<1) {
			$data = $this->log_new($config);
			$id = $data['id'];
		}

		unset($data);
		foreach($config as $key=>$val) {
			$data[$key] = $val;
			$fields[] = $key;
		}
		if(is_array($data['custom'])) {
			$data['custom'] = serialize($data['custom']);
		}

		$query = "UPDATE ".$this->SQL_table_log." SET ".$db->build(1,$fields,$data)." WHERE id = '{$id}'";
		if($db->query($query)) {
			return array("success"=>true,"id"=>$id);
		} else {
			return array("success"=>false);
		}
	}
	function log_delete($id=0) {
		global $db,$class_user;

		$query = "DELETE FROM ".$this->SQL_table_log." WHERE id = '".$id."' AND user_id = '".$class_user->authorised->id."'";
		if($db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function log_data($config=array()) {
		global $class_user,$zulu;

		$sql_config = array();
		$id = ($config['id']>0?$config['id']:0);

		if($config['object']!=NULL) {
			$sql_config['where'][] = "object = '".$config['object']."'";
		}
		if($config['object_id']!=NULL) {
			$sql_config['where'][] = "object_id = '".$config['object_id']."'";
		}
		if($config['title_find']!=NULL) {
			$sql_config['where'][] = "title LIKE '%".$config['title_find']."%'";
		}
		if($config['title']!=NULL) {
			$sql_config['where'][] = "title = '".$config['title']."'";
		}
		if($config['sort']!=NULL) {
			$sql_config['sort'] = $config['sort'];
		} else {
			$sql_config['sort'] = 'id DESC';
		}

		$sql_config['where'][] = "user_id = '".$class_user->authorised->id."'";
		return $zulu->table_data($this->SQL_table_log,$id,$sql_config);
	}
	function pdf_create($html, $dir, $filename='', $stream=TRUE) {

		$savein = dirname(__FILE__).'/../file/'.$dir;
		@mkdir($savein);
		$dompdf = new DOMPDF();
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->loadHtml($html);
		$dompdf->render();
		$canvas = $dompdf->get_canvas();
		$options = new Options();
		$options->setIsRemoteEnabled(true);
		$dompdf->setOptions($options);
		$pdf = $dompdf->output();      // gets the PDF as a string
		$path = $savein.str_replace("/","-",$filename);
		file_put_contents($path, $pdf);    // save the pdf file on server
		$dompdf->stream($path, array("Attachment" => false));exit;
		unset($html);
		unset($dompdf);
		return $path;
	}

	//-- Get File
	function file_download($filename) {
		//-------------START FORCE-----------------//
		if(ini_get('zlib.output_compression'))
		  ini_set('zlib.output_compression', 'Off');

		// addition by Jorg Weske
		$file_extension = strtolower(substr(strrchr($filename,"."),1));

		if( $filename == "" )
		{
		  echo "<html><title>RAZOR Downloader</title><body>ERROR: Download file NOT SPECIFIED.</body></html>";
		  exit;
		} elseif ( ! file_exists( $filename ) )
		{
		  echo "<html><title>RAZOR Downloader</title><body>ERROR: File not found.</body></html>";
		  exit;
		};
		switch( $file_extension )
		{
		  case "pdf": $ctype="application/pdf"; break;
		  case "exe": $ctype="application/octet-stream"; break;
		  case "zip": $ctype="application/zip"; break;
		  case "doc": $ctype="application/msword"; break;
		  case "xls": $ctype="application/vnd.ms-excel"; break;
		  case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		  case "gif": $ctype="image/gif"; break;
		  case "png": $ctype="image/png"; break;
		  case "jpeg":
		  case "jpg": $ctype="image/jpg"; break;
		  default: $ctype="application/force-download";
		}
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: $ctype");
		// change, added quotes to allow spaces in filenames, by Rajkumar Singh
		header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($filename));
		readfile("$filename");
		exit;
	}
	function timestamp($time) {
		$datesplit = explode("/",$time);
		return	mktime(0,0,0,$datesplit[1],$datesplit[0],$datesplit[2]);
	}
	function timestamp_future($time,$period) {
		return strtotime("+1 ".$period,$time);
	}
	function date_period($period,$custom_from=0,$custom_to=0) {
		if($period=='custom') {
			if($custom_from>0) {
				$return['start'] = $this->timestamp($custom_from);
			} else {
				$return['start'] = strtotime("-1 month");
			}
			if($custom_to>0) {
				$return['end'] = $this->timestamp($custom_to);
			} else {
				$return['end'] = strtotime("+1 month");
			}
		} else {
			$today = strtotime('tomorrow');
			$today_future = strtotime('today');
			switch($period) {
				case 'future':
				$return['end'] = strtotime("+10 years");
				$return['start'] = $today_future;
				$return['label'] = "All Upcoming";
				break;
				case 'last_7d': //-- 7 days ago
				$offset_end = true;
				$return['end'] = $today;
				$return['start'] = strtotime('-7 days',$return['end']);
				$return['label'] = "Last 7 days";
				break;
				case 'yesterday': //-- yesterday
				$offset_end = true;
				$return['end'] = strtotime("-1 day",$today);
				$return['start'] = strtotime("-1 day",$return['end']);
				$return['label'] = "Yesterday";
				break;
				case 'month_last': //-- last month
				$offset_end = true;
				$return['start'] = $this->timestamp('01/'.date("m/Y",strtotime("-1 month")));
				$return['end'] = strtotime("+1 month",$return['start']);
				$return['label'] = "Last Month";
				break;
				case 'last_30d': //-- 30 days ago
				$offset_end = true;
				$return['end'] = $today;
				$return['start'] = strtotime('-30 days',$return['end']);
				$return['label'] = "Last 30 days";
				break;
				case 'month_last': //-- last month
				$offset_end = true;
				$last_month =
				$return['end'] = $today;
				$return['start'] = strtotime('-1 month',$return['end']);
				$return['label'] = "Last 30 days";
				break;
				case 'week': //-- this week M-S
				$offset_end = true;
				if(date('N')==1) {
					$return['start'] = $today_future;
					$return['end'] = strtotime("+1 week",$return['start']);
				} else {
					$return['start'] = strtotime('last monday');
					$return['end'] = strtotime('next monday');
				}
				$return['label'] = "This Week";
				break;
				case 'week_last': //-- last week M-S
				$offset_end = true;
				if(date('N')==1) {
					$return['start'] = strtotime('last monday');
				} else {
					$return['start'] = strtotime('Last Monday - 1 week');
				}
				$return['end'] = strtotime("+1 week",$return['start']);
				$return['label'] = "Last Week";
				break;
				case 'month': //-- this month
				$offset_end = true;
				$return['start'] = $this->timestamp('1/'.date('m/Y'));
				$return['end'] = $this->timestamp(date('t/m/Y'));
				$return['label'] = "This Month";
				break;
				default: //-- today date
				$offset_end = true;
				$return['start'] = strtotime('today');
				$return['end'] = $return['start']+$this->config->tomorrow;
				$return['label'] = "Today";
				break;
			}
			if($offset_end) {
				$return['end'] -= 1;
			}
		}

		return $return;
	}
	function hex_convert($val) {
		if(!strstr($val,"#")) {
			switch($val) {
				case 'purple':
				$val = '#7209c3';
				break;
				case 'pink':
				$val = '#ee00ae';
				break;
				case 'yellow':
				$val = '#c37f09';
				break;
				case 'red':
				$val = '#C00';
				break;
				case 'green':
				$val = '#a4aa09';
				break;
				case 'orange':
				$val = '#f14400';
				break;
				default:
				$val = '#000';
				break;
			}
		}
		return $val;
	}
	function pagination($pos=1,$config=[]) {
		$pos = ($pos>0?$pos:1);
		$max = (isset($config['page_max_page'])&&$config['page_max_page']>0?$config['page_max_page']:$this->config->page_max_page);
		$to_show = (isset($config['page_max'])&&$config['page_max']>0?$config['page_max']:$this->config->page_max);
		$showing = $to_show;
		$total = ceil(($config['count']/$max));
		$link = $config['link'];
		if(!strstr($link,"?")) {
			$link .= "?";
		}
		if(($to_show % 2) > 0) $middle_show = ($to_show-1) / 2;
		else $middle_show = $to_show / 2;

		for($i=2; $i<$total; $i++) {
			if($pos <= ($to_show-1)) {
				if($showing > 1) {
					$html .= "<li".($pos==$i?" class=\"active\"":NULL)."><a ".($pos!=$i?"href=\"".$link."&Pg=".$i."\"":NULL).">".$i."</a></li>";
				} else {
					$html .= "<li class=\"disabled\"><a>...</a></li>";
					break;
				}
			} elseif($pos > ($total-$to_show+1)) {
				if($showing == $to_show) {
					$html .= "<li class=\"disabled\"><a>...</a></li>";
				} elseif($i >= ($total-$to_show+1)) {
					$html .= "<li".($pos==$i?" class=\"active\"":NULL)."><a ".($pos!=$i?"href=\"".$link."&Pg=".$i."\"":NULL).">".$i."</a></li>";
				}
			} else {
				if($i==($pos-$middle_show) || $i==($pos+$middle_show)) {
					$html .= "<li class=\"disabled\"><a>...</a></li>";
				} elseif($i>($pos-$middle_show) && $i<($pos+$middle_show)) {
					$html .= "<li".($pos==$i?" class=\"active\"":NULL)."><a ".($pos!=$i?"href=\"".$link."&Pg=".$i."\"":NULL).">".$i."</a></li>";
				}
			}
			$showing--;
		}
		if($total>1) {
		return "
			<ul class=\"pagination\">
				<li".($pos<=1?" class=\"disabled\"":NULL)."><a ".($pos>1?"href=\"".$link."&Pg=".($pos-1)."\"":NULL)." title='Previous Page'><i class='far fa-arrow-left'></i></a></li>
				<li".($pos<=1?" class=\"active\"":NULL)."><a ".($pos>1?"href=\"".$link."&Pg=1\"":NULL).">1</a></li>
				".$html."
				".($total>1?"<li".($pos>=$total?" class=\"active\"":NULL)."><a ".($pos<$total?"href=\"".$link."&Pg=".$total."\"":NULL).">".$total."</a></li>":NULL)."
				<li".($pos>=$total?" class=\"disabled\"":NULL)."><a ".($pos<$total?"href=\"".$link."&Pg=".($pos+1)."\"":NULL)." title='Next Page'><i class='far fa-arrow-right'></i></a></li>
			</ul>";
		}
	}
	function pagination_config($pg=NULL,$config=[]) {
		if($pg==NULL) {
			$pg = $_GET['Pg'];
		}

		$start = ($pg>1?$this->config->page_max_page*($pg-1):0);
		return ['page'=>$pg,'start'=>$start,'limit'=>$this->config->page_max_page];
	}

	function ends_with($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}
	//-- Export CSV
	function export_csv($data,$config=[]) {
		global $class_user,$class_data,$zulu;

		$root_path = "uploads/export-".$zulu->slug($config['name'])."-".$class_user->authorised->id."-".time().".csv";
		$file = fopen(dirname(__FILE__)."/../".$root_path, "w");
		fwrite($file,"Export of ".$config['name']."\n");
		fwrite($file,"Date:,".date('d/m/Y h:ia')."\n");
        if(!isset($config['skip_total']) || !$config['skip_total']) {
            fwrite($file,"Total:,".count($data['body'])."\n\n");
        }

		//-- loop heading row
		foreach($data['header'] as $row_content) {
			if(is_array($row_content)) {
				$content = $row_content['data'];
			} else {
				$content = $row_content;
			}
			$export_row[] = $content;
		}
		fputcsv($file, $export_row);
		unset($export_row);

		//-- loop through data
		foreach($data['body'] as $row) {
			foreach($row as $row_content) {
				if(is_array($row_content)) {
					$content = $row_content['data'];
				} else {
					$content = $row_content;
				}
				$export_row[] = $content;
			}
			fputcsv($file, $export_row);
			unset($export_row);
		}
		fclose($file);

		return ['success'=>true,'url'=>MAIN_url.$root_path];
	}
	function video_url($url,$config=[]) {
		if(strstr($url,"youtube.com") || strstr($url,"youtu.be")) { //youtube
			$pattern =
				'%^# Match any youtube URL
				(?:https?://)?  # Optional scheme. Either http or https
				(?:www\.)?      # Optional www subdomain
				(?:             # Group host alternatives
				  youtu\.be/    # Either youtu.be,
				| youtube\.com  # or youtube.com
				  (?:           # Group path alternatives
					/embed/     # Either /embed/
				  | /v/         # or /v/
				  | /watch\?v=  # or /watch\?v=
				  )             # End path alternatives.
				)               # End host alternatives.
				([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
				$%x'
				;
			$result = preg_match($pattern, $url, $matches);
			if (false !== $result) {
				$newurl = "https://www.youtube.com/embed/".$matches[1].'?';
			}
			$yt = true;
		} elseif(strstr($url,"vimeo.com")) {
			if (preg_match("/(?:https?:\/\/)?(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/", $url, $id)) {
				$videoId = $id[3];
			}
			$id = $matches[2];
			$newurl = "http://vimeo.com/moogaloop.swf?clip_id=".$videoId;
			$vm = true;
		}
		if($config['autoplay']&&$yt) {
			$newurl .= '&autoplay=1';
		}
		if($config['autoplay']&&$vm) {
			$newurl .= '&autoplay=1';
		}
		return $newurl;
	}
	function s($str) {
		return ($str!=1?'s':NULL);
	}
	function geocode($address,$region='new+zealand') {

		$address = str_replace(" ", "+", $address);
		$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
		$json = json_decode($json);

		$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

		return [
			'lat'=>$lat,
			'long'=>$long
		];
	}
	function js_prompt_copy($str,$config=[]) {
		$label = $str;
		if($config['shorten']>0) {
			$label = $this->shorten($label,$config['shorten']);
		}
		return "<a href=\"#\" title=\"Click to copy: ".$str."\" class=\"js-prompt-link\" onclick=\"prompt('Copy the information below...','".$str."');return false;\">".$label."</a>";
	}
	function colour_shade_adjust($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
    function entity($val, $config=[]) {

        if(!isset($config['double']) || !$config['double']) {
            $double_encode = false;
        } else {
            $double_encode = true;
        }
        if(!isset($config['flags']) || $config['flags'] != null) {
            $flags = ENT_QUOTES;
        } else {
            $flags = $config['flags'];
        }

        $val = htmlentities($val, $flags, null, $double_encode);

        return $val;
    }

    public static function modal($html='', $title='', $tag='', $config=[]) {
        global $zulu;

        if($tag == null) {
            $tag = $zulu->serial();
        }
        $is_form = (isset($config['form'])&&$config['form']?true:false);
        $size = (isset($config['size'])?$config['size']:'lg');

        $html = "
        <div id='modal-".$tag."' class='modal fade' role='dialog'>
            <div class='modal-dialog modal-".$size."'>
                <div class='modal-content'>
                    <div class='modal-header'><button type='button' class='close btn-close-modal' data-dismiss='modal'>&times;</button><h4 class='modal-title'>".$title."</h4></div>
					".($is_form?'<form method="'.($config['form_method']?:'POST').'">':NULL)."
                    <div class='modal-body'>".$html."</div>
                    <div class='modal-footer'>
						".($config['footer']?:"<button type='button' class='btn btn-default pull-left btn-close-modal' data-dismiss='modal'>Close</button>")."
                        ".($is_form?"<button type='submit' class='btn btn-success'><i class='fas fa-check'></i> Save</button>":null)."
                    </div>
					".($is_form?'</form>':NULL)."
                </div>
            </div>
        </div>";
        $zulu->template->jquery['modal'] = "
        $(document).on('click', '.modal-trigger', function() {
            var id = $(this).data('modal');
            $('.modal#'+id).modal('show');
            return false;
        });
        ";
        return [
            'html'  =>  $html,
            'id'    =>  "modal-".$tag,
        ];
    }

	public function icon($icon, $style='s', $classes=[]) {
        return "<span class='icon fa".$style." fa-".$icon.(count($classes)>0?" ".implode(' ',$classes):null)."'></span>";
    }

    public function include_owl_carousel() {
        $this->template->js_file['owl-carousel'] = FE_rel."template/default/assets/owl-carousel/dist/owl.carousel.min.js";
        $this->template->css_file['owl-carousel'] = FE_rel."template/default/assets/owl-carousel/dist/assets/owl.carousel.min.css";
    }

    public function include_tiny_slider() {
        $this->template->js_file['tiny-slider'] = FE_rel."template/default/assets/tiny-slider/dist/min/tiny-slider.js";
        $this->template->css_file['tiny-slider'] = FE_rel."template/default/assets/tiny-slider/dist/tiny-slider.css";
    }

    public function include_popup_overlay() {
        $this->template->js_file['popup-overlay'] = FE_tpl_rel."assets/popup-overlay/jquery.popupoverlay.js";
    }

    public function stringEncrypt($string='') {
        if(!function_exists('openssl_encrypt') || !$string) {
            return $string;
        }

        $method = "AES-256-CBC";
        $key = hash('sha256', $method, true);
        $iv = openssl_random_pseudo_bytes(16);

        $ciphertext = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

        return $iv . $hash . $ciphertext;
    }

    public function stringDecrypt($encrypted_string='') {
        if(!function_exists('openssl_encrypt') || !$encrypted_string) {
            return $encrypted_string;
        }

        $method = "AES-256-CBC";
        $iv = substr($encrypted_string, 0, 16);
        $hash = substr($encrypted_string, 16, 32);
        $ciphertext = substr($encrypted_string, 48);
        $key = hash('sha256', $method, true);

        if(!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) {
            return null;
        }

        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

	function file_upload_max_size() {
	  static $max_size = -1;

	  if ($max_size < 0) {
	    // Start with post_max_size.
	    $post_max_size = $this->parse_ini_size(ini_get('post_max_size'));
	    if ($post_max_size > 0) {
	      $max_size = $post_max_size;
	    }

	    // If upload_max_size is less, then reduce. Except if upload_max_size is
	    // zero, which indicates no limit.
	    $upload_max = $this->parse_ini_size(ini_get('upload_max_filesize'));
	    if ($upload_max > 0 && $upload_max < $max_size) {
	      $max_size = $upload_max;
	    }
	  }
	  return $max_size;
	}

	function parse_ini_size($size) {
	  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	  if ($unit) {
	    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
	    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	  }
	  else {
	    return round($size);
	  }
	}

	function dateTimezone($ts, $format='d/m/Y') {

		$dt = new DateTime();
		if(isset($_SESSION['TIMEZONE']) && $_SESSION['TIMEZONE']) {
			$dt->setTimezone(new DateTimeZone($_SESSION['TIMEZONE']));
		}
        $dt->setTimestamp($ts);
        $ts_text = $dt->format($format);

		return $ts_text;
	}

	function table_data_filter($config=[]) {
		global $db;
		$sql_config = [];

		if(isset($config['count']) && $config['count'] ) {
			$sql_config['first'] = true;
			$sql_config['field'][] = "COUNT(id) AS _total";
		}
		if(isset($config['filter'])) {
			foreach($config['filter'] as $wkey=>$wval) {
				if($wval=='') continue;
				$sql_config['where'][] = "`".$wkey."` = '".$db->escape_string($wval)."'";
			}
		}
		if(isset($config['filter_search']) && count($config['filter_search'])>0) {
			foreach($config['filter_search'] as $fkey=>$fval) {
				if(strlen($fval)<=0) {
					continue;
				}
				$sql_config['where'][] = "`".$fkey."` LIKE '%".$db->escape_string($fval)."%'";
			}
		}

        if(isset($config['field'])) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['where_txt']) && count($config['where_txt'])>0) {
			foreach($config['where_txt'] as $wval) {
				$sql_config['where'][] = $wval;
			}
		}
		if(isset($config['where']) && count($config['where'])>0) {
			foreach($config['where'] as $wkey=>$wval) {
				$sql_config['where'][] = "`".$wkey."` = '".$wval."'";
			}
		}
		if(isset($config['join'])){
			$sql_config['join'] = $config['join'];
		}
		if(isset($config['field'])) {
			$sql_config['field'] = $config['field'];
		}
		if(isset($config['first']) && $config['first']) {
			$sql_config['first'] = true;
		}
		if(isset($config['group'])) {
			$sql_config['group'] = $config['group'];
		}
		if(isset($config['sort'])) {
			$sql_config['sort'] = $config['sort'];
		}
		if(isset($config['row_limit']) &&  $config['row_limit']>0) {
			$sql_config['limit'] = $config['row_limit'];
		}
		if(isset($config['test']) && $config['test']) {
			$sql_config['test'] = true;
		}
		if(isset($config['row_start']) && $config['row_start']>0) {
			$sql_config['start'] = $config['row_start'];
		}
		return $sql_config;
	}

	function date_time_label($timestamp,$config=[]) {
		$output = $this->date($timestamp,'j/m/Y');

		if($this->date($timestamp,'Hi')>0 && (!isset($config['justdate']) || !$config['justdate'])) {
			$output .= " <sup>".$this->time_label($timestamp)."</sup>";
		}
		return $output;
	}

	function time_label($timestamp) {
		return $this->date($timestamp,'g:ia');
	}

	function object_name($object,$object_id,$short=false) {
		$object_request = $this->object_data($object,$object_id);
		if($short) {
			return $object_request['name'];
		} else {
			return $this->object_title($object).": ".$object_request['name'];
		}
	}

	function object_data($obj,$id) {
		global $zulu,$class_client,$class_sale,$class_project,$class_sale_quote,$class_task,$class_sorder,$class_porder,$class_shipment,$class_product_return,$class_product;

		switch ($obj) {
			case 'client':
				$link = $class_client->client_data(['id'=>$id]);
				$link['type'] = "Contact";
				break;
			case 'sale':
				$link = $class_sale->sale_data(['id'=>$id]);
				$link['name'] = $class_sale->reference($link['reference']);//." - ".$link['name']
				$link['type'] = $class_sale->name();
				break;
			case 'sale_line':
				$link = $class_sale->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_sale->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_sale->reference($sale['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_sale->name();
				break;
			case 'porder':
				$link = $class_porder->sale_data(['id'=>$id]);
				$link['name'] = $class_porder->reference($link['reference']);//." - ".$link['name']
				$link['type'] = $class_porder->name();
				break;
			case 'porder_line':
				$link = $class_porder->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_porder->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_porder->reference($link['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_porder->name();
				break;
			case 'sale_quote':
				$link = $class_sale_quote->sale_data(['id'=>$id]);
				$link['name'] = $class_sale_quote->reference($link['reference']);//." - ".$link['name'];
				$link['type'] = $class_sale_quote->name();
				break;
			case 'sale_quote_line':
				$link = $class_sale_quote->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_sale_quote->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_sale_quote->reference($link['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_sale_quote->name();
				break;
			case 'shipment':
				$link = $class_shipment->sale_data(['id'=>$id]);
				$link['name'] = $class_shipment->reference($link['reference']);//." - ".$link['name']
				$link['type'] = $class_shipment->name();
				break;
			case 'shipment_line':
				$link = $class_shipment->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_shipment->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_shipment->reference($sale['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_shipment->name();
				break;
			case 'sorder':
				$link = $class_sorder->sale_data(['id'=>$id]);
				$link['name'] = $class_sorder->reference($link['reference']);//." - ".$link['name']
				$link['type'] = $class_sorder->name();
				break;
			case 'sorder_line':
				$link = $class_sorder->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_sorder->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_sorder->reference($sale['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_sorder->name();
				break;
			case 'product_return':
				$link = $class_product_return->sale_data(['id'=>$id]);
				$link['name'] = $class_product_return->reference($sale['reference']);//." - ".$link['name']
				$link['type'] = $class_product_return->name();
				break;
			case 'product_return_line':
				$link = $class_product_return->sale_line_data(['id'=>$id]);
				if($link['sale_id']>0) {
					$sale = $class_product_return->sale_data(['id'=>$link['sale_id']]);
					$link['name'] = $class_product_return->reference($sale['reference']);//." - ".sale['name']
				}
				$link['type'] = $class_product_return->name();
				break;
			case 'job':
			case 'project':
				$link = $class_project->project_data(['id'=>$id]);
				$link['name'] = $class_project->reference($link['reference'])." - ".stripslashes($link['title']);
				$link['type'] = $class_project->name();
				break;
			case 'post':
				global $class_post;
				$post = $class_post->post_data(['id'=>$id,'field'=>['type','title','id']]);
				$link['name'] = stripslashes($post['title']);
				$link['type'] = $class_post->post_type_name($post['type']);
				break;
			case 'task':
				$link = $class_task->task_data(['id'=>$id,'field'=>['title']]);
				$link['name'] = stripslashes($link['title']);
				$link['type'] = "Task";
				break;
			case 'quote':
				global $class_quote;
				//$link = $class_quote->quote_data(['id'=>$id,'field'=>['reference','client_id','id']]);
				$link['name'] = $class_quote->reference($id);
				$link['type'] = "Quote";
				break;
			case 'product':
				$link = $class_product->product_data(['id'=>$id,'field'=>['name','sku']]);
				$link['name'] = ($link['sku']!=''?stripslashes($link['sku']).' - ':NULL).stripslashes($link['name']);
				$link['type'] = "Product";
				break;
			case 'product_batch':
				$link = $class_product->batch_data(['id'=>$id]);
				$link['name'] = $class_product->batch_reference($link['reference']);
				$link['type'] = "Product Batch";
				break;
			case 'pipeline_item':
				//$link = $class_product->batch_data(['id'=>$id]);
				$link['name'] = "Test ".$id;
				$link['type'] = "Pipeline Item";
				break;
			case 'sche_book':
				global $class_schedule;
				$link = $class_schedule->book_data(['id'=>$id,'field'=>['reference','date_start']]);
				$link['name'] = (trim($link['reference'])!=NULL?$link['reference']:NULL).' ('.$zulu->date($link['date_start']).')';
				$link['type'] = "Product";
				break;
			case 'note':
				$note_data = Note::query()->where('id',$id)->select(['title','stat_add'])->first($id);
				$link['name'] = $this->shorten($note_data->title,30).' ('.$zulu->date($note_data->stat_add,'j/m/Y').')';
				$link['type'] = "Note";
				break;
		}
		return $link;
	}

	public static function nl($str) {
		return (trim($str)==NULL?self::none():$str);
	}

	public static function none($hyphen=false) {
		return "<span class=\"opt opt-grey\">".($hyphen?'-':'None')."</span>";
	}

	public function include_select2() {
		$this->template->css_file['select2'] = "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css";
        $this->template->js_file['select2'] = "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js";
    }

}
