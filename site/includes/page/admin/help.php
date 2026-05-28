<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'help');
define(PAGE_name,'Resources & How-to\'s');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check();

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
	
	//Roles
	foreach(user::role_data() as $role_row) {
		$USER_option[$role_row['tag']] = $role_row['name'];
	}
	
	$form_edit = new form;
	
	$zulu->template->head = "";
	$zulu->template->body = "";
	
	if(PAGE_action==NULL) {
		$zulu->nav->title = PAGE_name;
		$form_edit = new form();
		$form_edit->categoryOptionForm('','procedure_category');
		
		$zulu->template->js_code[] = "
			$('body').on('change', 'select[name=Category]', function() {
				$('#filter').submit();
			});
		";
		$cid = ($_GET['Category']>0?$_GET['Category']:0);
		$cat_array = $class_procedure->category_children($cid);
		$cat_array[] = $cid;
		
		if($_GET['Search'] != NULL) {
			$search = $db->escape_string(strtolower($_GET['Search']));
			//echo "SELECT DISTINCT proc.id, proc.title FROM `procedure` AS proc LEFT JOIN procedure_step AS step ON proc.id=step.procedure_id WHERE proc.user_id='".$class_user->authorised->id."' AND (proc.title LIKE '%".$search."%' OR proc.description LIKE '%".$search."%' OR step.title LIKE '%".$search."%' OR step.description LIKE '%".$search."%')".($cid>0?" AND category IN (".implode(',',$cat_array).")":NULL)."";exit;
			$query = $db->mysqli->query("SELECT DISTINCT proc.id, proc.title FROM `procedure` AS proc LEFT JOIN procedure_step AS step ON proc.id=step.procedure_id WHERE proc.user_id='".$class_user->authorised->id."' AND (proc.title LIKE '%".$search."%' OR proc.description LIKE '%".$search."%' OR step.title LIKE '%".$search."%' OR step.description LIKE '%".$search."%')".($cid>0?" AND category IN (".implode(',',$cat_array).")":NULL)."") or die($db->mysqli->error);
			while($row = $query->fetch_assoc()) {
				$search_html .= "<li><a href=\"".$zulu->link_page('procedure',array('query'=>array('Action'=>'print','id'=>$row['id'],'Method'=>'procedure')))."\" class=\"popup-help\" data-fancybox-type=\"iframe\">".stripslashes($row['title'])."</a></li>";
			}
			if($search_html != NULL) {
				$search_html = "<ul>".$search_html."</ul>";
			} else {
				$search_html = "<p><i>There are no procedures here.</i></p>";
			}
			$zulu->template->search_results = $search_html;
		}
		
		$cat_data = $class_procedure->category_data(array('root_id'=>$cid));
		if(count($cat_data) > 0) {
			foreach($cat_data as $cat_row) {
				$cat2_data = $class_procedure->category_data(array('root_id'=>$cat_row['id']));
				if(count($cat2_data) > 0) {
					foreach($cat2_data as $cat2_row) {
						$cat3_data = $class_procedure->category_data(array('root_id'=>$cat2_row['id']));
						if(count($cat3_data) > 0) {
							foreach($cat3_data as $cat3_row) {
								$children = $class_procedure->category_children($cat3_row['id']);
								$children[] = $cat3_row['id'];
								$proc_data = $class_procedure->procedure_data(array('category_in'=>"(".implode(',',$children).")",'public'=>'1'));
								foreach($proc_data as $proc_row) {
									$level4_html .= "<li><a href=\"".$zulu->link_page('procedure',array('query'=>array('Action'=>'print','id'=>$proc_row['id'],'Method'=>'procedure')))."\" class=\"popup-help\" data-fancybox-type=\"iframe\">".stripslashes($proc_row['title'])."</a></li>";
								}
								$level3_html .= "<li><h4>".stripslashes($cat3_row['name'])."</h4><ul>".$level4_html."</ul></li>";
								unset($level4_html);
							}
							if(count($proc_data) > 0) {
								$level3_html = "<ul>".$level3_html."</ul>";
								unset($proc_data);
							} else {
								$level3_html = "<p><i>There are no procedures here.</i></p>";
							}
						} else {
							$proc_data = $class_procedure->procedure_data(array('category'=>$cat2_row['id'],'public'=>'1'));
							if(count($proc_data) > 0) {
								foreach($proc_data as $proc_row) {
									$level3_html .= "<li><a href=\"".$zulu->link_page('procedure',array('query'=>array('Action'=>'print','id'=>$proc_row['id'],'Method'=>'procedure')))."\" class=\"popup-help\" data-fancybox-type=\"iframe\">".stripslashes($proc_row['title'])."</a></li>";
								}
								$level3_html = "<ul>".$level3_html."</ul>";
								unset($proc_data);
							} else {
								$level3_html = "<p><i>There are no procedures here.</i></p>";
							}
						}
						$level2_html .= "<li><h3>".stripslashes($cat2_row['name'])."</h3>".$level3_html."</li>";
						unset($level3_html);
					}
					$level2_html = "<ul>".$level2_html."</ul>";
				} else {
					$proc_data = $class_procedure->procedure_data(array('category'=>$cat_row['id'],'public'=>'1'));
					if(count($proc_data) > 0) {
						foreach($proc_data as $proc_row) {
							$level2_html .= "<li><a href=\"".$zulu->link_page('procedure',array('query'=>array('Action'=>'print','id'=>$proc_row['id'],'Method'=>'procedure')))."\" class=\"popup-help\" data-fancybox-type=\"iframe\">".stripslashes($proc_row['title'])."</a></li>";
						}
						$level2_html = "<ul>".$level2_html."</ul>";
						unset($proc_data);
					} else {
						$level2_html = "<p><i>There are no procedures here.</i></p>";
					}
				}
				$list_html .= "
					<div class=\"panel panel-default\">
						<div class=\"panel-heading\">
							<h4 class=\"panel-title\">
								<a href=\"#collapse".$cat_row['id']."\" data-parent=\"#accordion\" data-toggle=\"collapse\">".stripslashes($cat_row['name'])."</a>
							</h4>
						</div>
						<div id=\"collapse".$cat_row['id']."\" class=\"panel-collapse collapse\">
							<div class=\"panel-body\">
								".$level2_html."
							</div>
						</div>
					</div>";
				unset($level2_html);
			}
		} else {
			$proc_data = $class_procedure->procedure_data(array('category'=>$cid,'public'=>'1'));
			if(count($proc_data) > 0) {
				foreach($proc_data as $proc_row) {
					$list_html .= "<li><a href=\"".$zulu->link_page('procedure',array('query'=>array('Action'=>'print','id'=>$proc_row['id'],'Method'=>'procedure')))."\" class=\"popup-help\" data-fancybox-type=\"iframe\">".stripslashes($proc_row['title'])."</a></li>";
				}
				$list_html = "<ul>".$list_html."</ul>";
				unset($proc_data);
			} else {
				$list_html = "<p><i>There are no procedures here.</i></p>";
			}
		}
		$zulu->template->procedure_list = "<div id=\"accordion\" class=\"panel-group\">".$list_html."</div>";
	}
}