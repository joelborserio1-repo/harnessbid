<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- DEFINITIONS
define(PAGE_file,'index');
define(PAGE_name,'Dashboard');
$zulu->nav->breadcrumb[PAGE_name] = array("link"=>$zulu->link_page(PAGE_file));

//-- AUTHORISED?
$class_user->user_authorised_check(PAGE_action);

//-- ADMIN MASTER SECTION
if(MASTER_section=='admin') { //admin section
	//-- Web Mode
	if(MASTER_mode=='web') {

		//-- Pages
		$table_column[] = array("Page",array('class'=>array('')));
		$table_column[] = array("Modified",array('class'=>array('center')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_post->post_data(array('type'=>'page','limit'=>5,'sort'=>'stat_update DESC'));
		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($zulu->time_history($row['stat_update'])),
				array("<a href=\"".$zulu->link_page('post',['query'=>['Action'=>'edit','id'=>$row['id']]])."\" title=\"Edit this page.\" class=\"btn btn-primary btn-circle\"><i class=\"fas fa-edit\"></i></a>
				<a title=\"View this page.\" target=\"_blank\" href=\"".$class_post->post_url($row['id'])."\" class=\"btn btn-default btn-circle\"><i class=\"fas fa-search\"></i></a>",array('class'=>array('right','w100'))))
			);
			$total_row++;
		}
		$count_lead = $total_row;
		$zulu->template->table_page = ($count_lead>0?$zulu->table_render($table_row,0,array('class'=>['table-small'],'data_table'=>false)):"<p class=\"color-grey no-margin\"><span class=\"fas fa-times\"></span> No pages yet!</p>");
		unset($table_column,$table_row);

		//-- News
		$table_column[] = array("News",array('class'=>array('')));
		$table_column[] = array("Modified",array('class'=>array('center')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_post->post_data(array('type'=>'news','limit'=>5,'sort'=>'stat_update DESC'));
		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($zulu->time_history($row['stat_update'])),
				array("<a href=\"".$zulu->link_page('post',['query'=>['Action'=>'edit','id'=>$row['id']]])."\" title=\"Edit this page.\" class=\"btn btn-primary btn-circle\"><i class=\"fas fa-edit\"></i></a>
				<a title=\"View this page.\" target=\"_blank\" href=\"".$class_post->post_url($row['id'])."\" class=\"btn btn-default btn-circle\"><i class=\"fas fa-search\"></i></a>",array('class'=>array('right','w100'))))
			);
			$total_row++;
		}
		$count_lead = $total_row;
		$zulu->template->table_news = ($count_lead>0?$zulu->table_render($table_row,0,array('class'=>['table-small'],'data_table'=>false)):"<p class=\"color-grey no-margin\"><span class=\"fas fa-times\"></span> No news yet!</p>");
		unset($table_column,$table_row);

		//-- Menu
		$table_column[] = array("Menu",array('class'=>array('')));
		$table_column[] = array("Modified",array('class'=>array('center')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_post->post_data(array('type'=>'menu','limit'=>5,'sort'=>'stat_update DESC'));
		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
				array(stripslashes($row['title'])),
				array($zulu->time_history($row['stat_update'])),
				array("<a href=\"".$zulu->link_page('post',['query'=>['Action'=>'edit','id'=>$row['id']]])."\" title=\"Edit this page.\" class=\"btn btn-primary btn-circle\"><i class=\"fas fa-edit\"></i></a>",array('class'=>array('right','w100'))))
			);
			$total_row++;
		}
		$count_lead = $total_row;
		$zulu->template->table_menu = ($count_lead>0?$zulu->table_render($table_row,0,array('class'=>['table-small'],'data_table'=>false)):"<p class=\"color-grey no-margin\"><span class=\"fas fa-times\"></span> No menus yet!</p>");
		unset($table_column,$table_row);

	} else {
		$task_data = $class_task->task_data(array('opt_invoice=0'));
		$status = [0,0,0];
		foreach($task_data as $task) {
			switch($task['status']) {
				case 0:
					$status[0]++;
					break;
				case 1:
					$status[1]++;
					break;
				case 2;
					$status[2]++;
					break;
			}
		}

		$date_max = strtotime('next Sunday') + 86400;
		$date_min = strtotime('last Monday') - (6*604800);
		$date_array = [
			1	=>	['min'=>$date_min,'max'=>strtotime('+1 week',$date_min)],
			2	=>	['min'=>strtotime('+1 week',$date_min),'max'=>strtotime('+2 weeks',$date_min)],
			3	=>	['min'=>strtotime('+2 weeks',$date_min),'max'=>strtotime('+3 weeks',$date_min)],
			4	=>	['min'=>strtotime('+3 weeks',$date_min),'max'=>strtotime('+4 weeks',$date_min)],
			5	=>	['min'=>strtotime('+4 weeks',$date_min),'max'=>strtotime('+5 weeks',$date_min)],
			6	=>	['min'=>strtotime('+5 weeks',$date_min),'max'=>$date_max],
		];

		$task_data = $class_task->task_data(array("date_max"=>$date_max,"date_min"=>$date_min));
		foreach($task_data as $row) {
			for($i=1; $i<=count($date_array); $i++) {
				if($row['stat_add']>=$date_array[$i]['min'] && $row['stat_add']<$date_array[$i]['max']) {
					$chart_data[$i] += 1;
					break;
				}
			}
		}
		foreach($chart_data as $row=>$val) {
			$chart_row[] = "[".strtotime("+".$row." week",$date_min)."000, ".$val."]";
		}

		$client_data = $class_client->client_data(array("date_max"=>$date_max,"date_min"=>$date_min,'type'=>'all'));
		foreach($client_data as $row) {
			for($i=1; $i<=count($date_array); $i++) {
				if($row['stat_add']>=$date_array[$i]['min'] && $row['stat_add']<$date_array[$i]['max']) {
					$chart_data_lead[$i] += 1;
					break;
				}
			}
		}
		/*
		foreach($chart_data_lead as $row=>$val) {
			$chart_row_lead[] = "[".strtotime("+".$row." week",$date_min)."000, ".$val."]";
		}*/

		//-- CLIENT CONVERSION RATE
		/*$lead_data = $zulu->table_data("client",0,['field'=>['period AS lead_time','client.id','company'],'join'=>'client_status cs_lead ON client.id = cs_lead.client_id','where'=>["user_id = '".$class_user->authorised->id."'","status = 1","cs_lead.type = '2'"],'sort'=>'client.id ASC']);
		$convert_data = $zulu->table_data("client",0,['field'=>['client.id','company'],'join'=>'client_status cs_lead ON client.id = cs_lead.client_id JOIN client_status cs_client ON client.id = cs_client.client_id','where'=>["user_id = '".$class_user->authorised->id."'","status = 1","cs_lead.type = '2'","cs_client.type = '1'"],'sort'=>'client.id ASC']);*/

		$period = 4;
		$time_base = strtotime("-".$period." weeks");
		$lead_data = $zulu->table_data("client",0,['field'=>['cs_lead.time AS time','period AS lead_time','client.id','company'],'join'=>'client_status cs_lead ON client.id = cs_lead.client_id','where'=>["user_id = '".$class_user->authorised->id."'","status = 1","cs_lead.type = '2'","cs_lead.time >= '".$time_base."'"],'sort'=>'client.id ASC']);
		$convert_data = $zulu->table_data("client",0,['field'=>['cs_client.time AS time','client.id','company'],'join'=>'client_status cs_lead ON client.id = cs_lead.client_id JOIN client_status cs_client ON client.id = cs_client.client_id','where'=>["user_id = '".$class_user->authorised->id."'","status = 1","cs_lead.type = '2'","cs_client.type = '1'","cs_lead.time >= '".$time_base."'"],'sort'=>'client.id ASC']);
		//this code will show graph where conversions made are graphed on the DATE they were converted. TO show converted number where the actual client was generated, reply cs_client.time with cs_lead.time in the convert data field lookup.

		for($i=1;$i<=$period;$i++) {
			$timebase[$i] = [strtotime("-".$i." weeks"),strtotime("-".($i-1)." weeks")];
		}

		if(count($lead_data)>0&&count($convert_data)>0) {
			$conversion_rate = count($convert_data)/count($lead_data);
		}

		foreach($lead_data as $key=>$val) {
			foreach($timebase as $tkey=>$time) {
				if($val['time']>=$time[0]&&$val['time']<=$time[1]) {
					$total[$tkey]['lead']++;
				}
			}
		}
		foreach($convert_data as $key=>$val) {
			foreach($timebase as $tkey=>$time) {
				if($val['time']>=$time[0]&&$val['time']<=$time[1]) {
					$total[$tkey]['convert']++;
				}
			}
		}

		foreach($total as $row=>$val) {
			//$chart_row_lead[] = "{y:'".date("d/m/Y",$timebase[$row][0])."',a:".$val['lead'].",b:".$val['convert']."}";
		}
		foreach($timebase as $key=>$val) {
			$chart_row_lead[] = "{y:'".date("d M",$val[1])." - ".date("d M",$val[0])."',a:".($total[$key]['lead']>0?$total[$key]['lead']:0).",b:".($total[$key]['convert']>0?$total[$key]['convert']:0)."}";
		}

		//--
		//PANEL - NEW LEADS
		function edit_bt_lead($id) {
			global $zulu;
			return "
				<a href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'to_client')))."\" title=\"Convert lead to client\"><button class=\"btn btn-success btn-circle\" type=\"button\"><i class=\"fas fa-check\"></i></button></a>
				<a title=\"Edit this lead\" href=\"".$zulu->link_page('client',array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
			";
		}
		$total_row = 0;
		$table_column[] = array("Added",array('class'=>array('')));
		$table_column[] = array("Name",array('class'=>array('center')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_client->client_data(array('type'=>2,'limit'=>5));
		foreach($data_row as $row) {
			$table_row[] = array("content" => array(
				array(zulu::date($row['stat_add'],'d/m/Y')),
				array(stripslashes($row['name'])),
				array(edit_bt_lead($row['id']),array('class'=>array('right','w100'))))
			);
			$total_row++;
		}
		$count_lead = $total_row;
		$zulu->template->body->view_new_client = ($count_lead>0?$zulu->table_render($table_row,0,array('class'=>['table-small'],'data_table'=>false)):"<p class=\"color-grey no-margin\"><span class=\"fas fa-times\"></span> No new leads.</p>");
		unset($table_column,$table_row);

		//PANEL - TASKS
		function edit_bt($id) {
			global $zulu;
			return "
				<a title=\"Set this task to 'Pending Approval'\" href=\"".$zulu->link_page('task',array('query'=>array('id'=>$id,'Action'=>'set_status','Status'=>1)))."\"><button class=\"btn btn-success btn-circle\" type=\"button\"><i class=\"fas fa-check\"></i></button></a>
				<a title=\"Edit this task\" href=\"".$zulu->link_page('task',array('query'=>array('id'=>$id,'Action'=>'edit')))."\"><button class=\"btn btn-primary btn-circle\" type=\"button\"><i class=\"fas fa-edit\"></i></button></a>
			";
		}

		$table_column[] = array("Status",array('class'=>array('')));
		$table_column[] = array("Task",array('class'=>array('center')));
		$table_column[] = array("Client",array('class'=>array('center')));
		//$table_column[] = array("Time",array('class'=>array('center')));
		//$table_column[] = array("Billable",array('class'=>array('')));
		//$table_column[] = array("Added",array('class'=>array('right')));
		$table_column[] = array("Actions",array('class'=>array('right')));
		$table_row[] = array(
				"header"	=>	 true,
				"class"		=>	"",
				"content"	=>	$table_column);

		$data_row = $class_task->task_data(array('status'=>0,'limit'=>5));
		$incomplete_count = 0;
		foreach($data_row as $row) {
			$overdue = ($row['date_due']>0&&$row['date_due']<time()?true:false);
			if($row['status']<=1) {
				$incomplete_count++;
			}
			if($row['time']!=0) {
				$time = zulu::time_encode($row['time']);
				$hours = zulu::leadingZero($time['h']);
				$minutes = $time['m'];
			} else {
				$hours = "";
				$minutes = "";
			}
			if($row['client_id']>0) {
				$client_data = $class_client->client_data(array('id'=>$row['client_id'],'type'=>'all'));
			}
			if($row['job_id']>0) {
				$project_data = $class_project->project_data(array('id'=>$row['job_id']));
			}

			$table_row[] = array("content" => array(
				array(($row['status']==2?"Complete":($row['date_due']>0&&$row['date_due']<time()?"<b>Overdue! ".zulu::date($row['date_due'],'d/m/Y')."</b>":"Added ".zulu::date($row['stat_add'],'d/m/Y')))),
				array(stripslashes($row['title'])),
				//array($hours.":".$minutes,array('class'=>array('time'))),
				//array(($row['opt_bill']==1?"<span class=\"far fa-money-bill\"></span>":NULL),array('class'=>array('center'))),
				array(($row['job_id']>0?"<a href=\"".$zulu->link_page('project',array('query'=>array('id'=>$row['job_id'],'Action'=>'edit')))."\">".$project_data['title']."</a>":"<a href=\"".$zulu->link_page('client',array('query'=>array('id'=>$row['client_id'],'Action'=>'edit')))."\">".$client_data['name']."</a>")),
				array(edit_bt($row['id']),array('class'=>array('right','w100')))
			),'class'=>($row['status']==2?"green":($overdue?"yellow":NULL)));
			if($row['status']<2) {
				$count_task_pend++;
			}
			$total_row++;
		}
		$count_task = $total_row;
		$zulu->template->body->view_outstanding_tasks = ($count_task>0?$zulu->table_render($table_row,0,array('class'=>['table-small'],'data_table'=>false)):"<p class=\"color-grey no-margin\"><span class=\"fas fa-thumbs-up\"></span> All caught up! No tasks due.</p>");

		//-- LOAD JS CODE
		$zulu->config->chart_flot_js = true;
		$zulu->template->js_code[] = "
			$(function() {
				var data = [
					{ label: \"&nbsp;Incomplete\", data: ".$status[0].", color: '#AED7FA' },
					{ label: \"&nbsp;Awaiting Approval\", data: ".$status[1].", color: '#EEC32E' },
					{ label: \"&nbsp;Complete\", data: ".$status[2].", color: '#49A848' }
				];
				$.plot('#chart-task', data, {
					series: {
						pie: {
							show: true
						}
					},
					grid: {
						hoverable: true
					},
					tooltip: true,
					tooltipOpts: {
						content: \"%y.0, %s\", // show percentages, rounding to 2 decimal places
						shifts: {
							x: 20,
							y: 0
						},
						defaultTheme: true
					}
				});

				var barOptionsTask = {
					series: {
						bars: {
							show: true,
							barWidth: 604800000
						}
					},
					xaxis: {
						mode: \"time\",
						timeformat: \"%d/%m/%y\",
						minTickSize: [7, \"day\"]
					},
					yaxis: {
						minTickSize: 1
					},
					grid: {
						hoverable: true
					},
					legend: {
						show: false
					},
					tooltip: true,
					tooltipOpts: {
						content: \"Week From: %x, Total Tasks: \%y.0\"
					},
					colors: [\"#5cb85c\"]
				};
				var barDataTask = {
					label: \"bar\",
					data: [
					   ".implode(",\n",$chart_row)."
					]
				};
				$.plot($(\"#chart-task-bar\"), [barDataTask], barOptionsTask);

				var barOptionsLead = {
					series: {
						bars: {
							show: true,
							barWidth: 604800000
						}
					},
					xaxis: {
						mode: \"time\",
						timeformat: \"%d/%m/%y\",
						minTickSize: [7, \"day\"]
					},
					yaxis: {
						minTickSize: 1
					},
					grid: {
						hoverable: true
					},
					legend: {
						show: false
					},
					tooltip: true,
					tooltipOpts: {
						content: \"Week From: %x, Total Leads: \%y.0\"
					},
					colors: [\"#5cb85c\"]
				};
				var barDataLead = {
					label: \"bar\",
					data: [
					   ".implode(",\n",$chart_row_lead)."
					]
				};
				//$.plot($(\"#chart-lead-bar\"), [barDataLead], barOptionsLead);


			//-- Lead generation

			Morris.Bar({
				element: 'chart-lead-bar',
				data: [".implode(",\n",$chart_row_lead)."],
				xkey: 'y',
				ykeys: ['a', 'b'],
				labels: ['Leads', 'Conversions'],
				hideHover: 'auto',
				resize: true
			});
			});";
	}
}

$zulu->nav->title = PAGE_name;
