<?php
//(C)2015 RAZOR WEB DESIGN LIMITED
//DO NOT COPY WITHOUT PERMISSION
//V1.0.0

//-- Class: XERO
class xero {
	function __construct($config=[]) {
		global $db,$CONS_key,$SHARED_key,$class_setting,$zulu,$XeroOAuth,$class_user,$class_sale,$XERO_INCLUDED;
		$this->db = $db;
		$this->zulu = $zulu;
		$this->xero = $XeroOAuth;
		$this->config = $this->vars = new stdClass();

		$this->config->code_default = ($class_setting->data['xero_default_acc_code']!=NULL?$class_setting->data['xero_default_acc_code']:200);
		$this->config->tax = ($class_sale->gst_excl?'Exclusive':'Inclusive');

		if($class_setting->data['xero_consumer_key']!=NULL) {
			$this->vars->enabled = true;
		}

		/**
		 * Define which app type you are using:
		 * Private - private app method
		 * Public - standard public app method
		 * Public - partner app method
		 */
		define ( "XRO_APP_TYPE", ($class_setting->data['xero_consumer_key']!=NULL?"Private":"Public"));
		if(!$XERO_INCLUDED) {
			include(dirname(__FILE__)."/../xero/".strtolower(XRO_APP_TYPE).".php");
			$XERO_INCLUDED = true;
		}
		$this->temp = strtolower(XRO_APP_TYPE);
		if($_SESSION['oauth']['oauth_token']!=NULL) {
			$this->vars->enabled = true;
			$this->connect();
		}
		if($_SESSION['access_token']!=NULL) {
			$this->vars->enabled = true;
			$this->vars->connected = true;
			$this->connect(['persist'=>true]);
		}
	}
	function disconnect() {
		global $class_setting;
		unset($_SESSION['oauth'],$_SESSION['oauth_token_secret'],$_SESSION['access_token'],$_SESSION['session_handle']);

		$class_setting->setting_delete('xero_oauth_token');
		$class_setting->setting_delete('xero_oauth_token_secret');
		$class_setting->setting_delete('xero_oauth_session');
		$class_setting->construct(['cache_clear'=>true]);

		return true;
	}
	function connect($config=[]) {
		global $db,$CONS_key,$SHARED_key,$class_setting,$zulu,$XeroOAuth,$oauthSession;
		$oauthSession = retrieveSession();
//		if($_SERVER['REMOTE_ADDR'] == '103.3.232.17') {
//	print_r($class_setting->data['xero_oauth_token']);exit;
//}
		if(XRO_APP_TYPE=='Private') {
			if(!isset($_SESSION['access_token'])) {
				 $response = $XeroOAuth->refreshToken($class_setting->data['xero_oauth_token'], $class_setting->data['xero_oauth_session']);
				if ($XeroOAuth->response['code'] == 200) {
					$session = persistSession($response);
					$oauthSession = retrieveSession();
				} else {
					//echo $XeroOAuth->response['helper'];exit;
					//outputError($XeroOAuth);
					if($XeroOAuth->response['helper'] == "TokenFatal") {

						//--Disauth
						unset($_SESSION['access_token'],$_SESSION['oauth_token_secret'],$_SESSION['session_handle']);
						$class_setting->setting_delete('xero_oauth_token');
						$class_setting->setting_delete('xero_oauth_token_secret');
						$class_setting->setting_delete('xero_oauth_session');
						$class_setting->construct(['cache_clear'=>true]);

						//--Reauth
						$this->auth();
					}
					if($XeroOAuth->response['helper'] == "TokenExpired") {
						$XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
					}
				}
			}
		} elseif(XRO_APP_TYPE=='Public') {
			if (isset( $_REQUEST ['oauth_verifier'])) {
				$XeroOAuth->config ['access_token'] = $_SESSION ['oauth'] ['oauth_token'];
				$XeroOAuth->config ['access_token_secret'] = $_SESSION ['oauth'] ['oauth_token_secret'];

				$code = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'AccessToken', '' ), array (
						'oauth_verifier' => $_REQUEST ['oauth_verifier'],
						'oauth_token' => $_REQUEST ['oauth_token']
				) );

				if ($XeroOAuth->response ['code'] == 200) {

					$response = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );
					$session = persistSession ( $response );

					$class_setting->setting_edit('xero_access_token',$response['oauth_token']);
					$class_setting->setting_edit('xero_oauth_session',$_SESSION['session_handle']);

					$class_setting->setting_edit('xero_oauth_token',$response['oauth_token']);
					$class_setting->setting_edit('xero_oauth_token_secret',$response['oauth_token_secret']);
					$class_setting->construct(['cache_clear'=>true]);

					unset ($_SESSION['oauth']);
					$zulu->notification_set("Connected to Xero&reg; successfully.",1);
					header ( "Location: ".$zulu->link_page('setting',['self'=>true,'filter'=>['Do','oauth_token','oauth_verifier']]));
					exit;
				} else {
					outputError ($XeroOAuth);
				}
			} else {
				if($config['persist']) {
					$oauthSession = retrieveSession();
					$XeroOAuth->config['access_token']  = $oauthSession['oauth_token'];
					$XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
					$XeroOAuth->config['session_handle'] = $oauthSession['oauth_session_handle'];
				} else {
					$oauthSession = retrieveSession();
					$response = $XeroOAuth->refreshToken($class_setting->data['xero_oauth_token'], $class_setting->data['xero_oauth_session']);
					if ($XeroOAuth->response['code'] == 200) {
						$session = persistSession($response);
						$oauthSession = retrieveSession();
					} else {
						//echo $XeroOAuth->response['helper'];exit;
						//outputError($XeroOAuth);
						if($XeroOAuth->response['helper'] == "TokenFatal") {

							//--Disauth
							unset($_SESSION['access_token'],$_SESSION['oauth_token_secret'],$_SESSION['session_handle']);
							$class_setting->setting_delete('xero_oauth_token');
							$class_setting->setting_delete('xero_oauth_token_secret');
							$class_setting->setting_delete('xero_oauth_session');

							//--Reauth
							$this->auth();
						}
						if($XeroOAuth->response['helper'] == "TokenExpired") {
							$XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
						}
					}
				}
			}
		}
	}
	function auth() {
		global $db,$CONS_key,$SHARED_key,$class_setting,$zulu,$XeroOAuth;

		$params = array (
				'oauth_callback' => OAUTH_CALLBACK
		);
		$response = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'RequestToken', '' ), $params );
		if ($XeroOAuth->response ['code'] == 200) {

			$scope = "";
			if ($_REQUEST ['XeroAuth'] > 1)
				$scope = 'payroll.employees,payroll.payruns,payroll.timesheets';
			$vars =  $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );

			$class_setting->setting_edit('xero_oauth_token',$vars['oauth_token']);
			$class_setting->setting_edit('xero_oauth_token_secret',$vars['oauth_token_secret']);
			$class_setting->construct(['cache_clear'=>true]);

			$_SESSION ['oauth'] = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );

			$authurl = $XeroOAuth->url ( "Authorize", '' ) . "?oauth_token={$_SESSION['oauth']['oauth_token']}&scope=" . $scope;

			header("Location: ".$authurl);
			exit;
		} else {
			outputError ( $XeroOAuth );
		}
	}

	/*
	//Build Sale XML Request
	function invoice_build($sale_id=array()) {
		global $class_sale;
		global $class_client;

		foreach($sale_id as $id) {
			$line_item = array();
			$bill_link = $class_sale->sale_line($id);

			foreach($bill_link as $bill_data) {

				$line_info = zulu::shorten(strip_tags(str_replace(array("<br>"),array("\n"),stripslashes($bill_data['description']))),80);
				$line_info = (trim($line_info)!=NULL?"\n".$line_info:NULL);

				$line_item[] = "
				<LineItem>
					<Description>{$line_info}</Description>
					<Quantity>".$bill_data['quantity']."</Quantity>
					<UnitAmount>".$bill_data['price']."</UnitAmount>
					<AccountCode>200</AccountCode>
					<DiscountRate>".$bill_data['discount']."</DiscountRate>
				  </LineItem>
				  ";
			}

			//Invoice Data
			$sale_data = $class_sale->sale_data(array('id'=>$id));
			$sale_meta = $class_sale->sale_meta($id);
			$sale_line = $class_sale->sale_line($id);
			$user_data = $class_client->client_data(array('id'=>$sale_data['client_id']));
			$invoice_data = $class_sale->sale_data(array('id'=>$id));

			$date = new DateTime;
			$date->setTimestamp($invoice_data['date']);

			$date_due = new DateTime;
			$date_due->setTimestamp($invoice_data['date_due']);

			//Client Info
			$client_data = $class_client->client_data(['id'=>$invoice_data['client_id']]);
			$client_custom_invoice_reference = $class_client->client_meta($client_data['id'],'custom_invoice_reference');
			if($client_data['xero_id']!=NULL) {
				$contact_info = "<ContactID>".$client_data['xero_id']."</ContactID>";
			} else {
				$contact_info = "<Name>".htmlentities(stripslashes($invoice_data['name']))."</Name>";
				if($invoice_data['email']!=NULL) {
					$contact_info .= "<EmailAddress>".htmlentities(stripslashes($invoice_data['email']))."</EmailAddress>";
				}
			}
			if(trim($sale_meta['xero_link']['value'])!=NULL) {
				$skip = true;
			}


			//Invoice Row
			if(!$skip) {
			$invoice_row[] = "
			<Invoice>
				<Type>ACCREC</Type>
				<Contact>
					{$contact_info}
				</Contact>
				<Date>".$date->format(DateTime::ISO8601)."</Date>
				<DueDate>".$date_due->format(DateTime::ISO8601)."</DueDate>
				<LineAmountTypes>Exclusive</LineAmountTypes>
				<LineItems>
				  ".implode("\n",$line_item)."
				</LineItems>
			  </Invoice>
					  ";
					  $log[] = "Sale #".$invoice_data['reference']." - Exported OK";
			} else {
					  $log[] = "Sale #".$invoice_data['reference']." - Export skipped, duplicate entry. <a href=\"".$this->zulu->link_page('sale',array('query'=>array('Action'=>'DumpXeroMeta','id'=>$id)))."\" class=\"confirm\">DELETE</a>";
			}
			 $invoice_id[] = $invoice_data['id'];
			 $skip = false;
		}

		//Compile Final XML
		 if(count($invoice_row)>0) {
			$xml = "<Invoices>
			".implode("\n",$invoice_row)."
			</Invoices>";
		 }
		return array('xml'=>$xml,'count'=>count($invoice_row),'sale_id_array'=>$invoice_id,'log'=>$log);
	}

	//Process Sale Invoice Upload
	function invoice_run($xml,$invoice_id) {
		global $zulu,$XeroOAuth;

		$response = $XeroOAuth->request('POST', $XeroOAuth->url('Invoices', 'core'), array(), $xml);
		if ($XeroOAuth->response['code'] == 200) {
			$invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);

			$i = 0;
			foreach($invoice_id as $inv_id) {
				$zulu->meta_update('sale',$inv_id,'xero_link',$invoice->Invoices->Invoice[$i]->InvoiceID);
				$i++;
			}
			return array("success"=>true,"message"=>"Invoice exported to Xero.");
		} else {
			//outputError($XeroOAuth);
			return array("success"=>false,"message"=>"Error exporting to Xero.");
		}
	}*/



	//Build Sale XML Request
	function invoice_build($sale_id=array(),$config=[]) {
		global $class_sale,$class_product,$class_client,$class_book,$zulu;

		foreach($sale_id as $id) {
			$line_item = array();
			$bill_link = $class_sale->sale_line($id);
			$reference_label ='';

			foreach($bill_link as $bill_data) {

				$code_override = NULL;
				$product_data = NULL;
				if($bill_data['product_id']>0) {
					$product_data = $class_product->product_data(['id'=>$bill_data['product_id']]);
					$product_meta = $zulu->meta_array($class_product->product_meta($bill_data['product_id']));
					$code_override = $product_meta['xero_account'];

					$stock_adjust = unserialize($product_meta['stock_adjust']);
					if(count($stock_adjust)>0) {
						foreach($stock_adjust as $sa) {
							$sub_product = $class_product->product_data(['id'=>$sa['product_id']]);
							$sub_xero = $zulu->meta_value('product',$sa['product_id'],'xero_account');
							$sub_xero_ignore = $zulu->meta_value('product',$sa['product_id'],'xero_ignore');
							$sub_code_override = $sub_xero['value'];

							$sub_line_info = zulu::shorten(strip_tags(str_replace(array("<br>"),array("\n"),stripslashes($sub_product['name']))),200);
							$sub_line_info = (trim($sub_line_info)!=NULL?"\n".$sub_line_info:NULL);

							$main_line_info = zulu::shorten(strip_tags(str_replace(array("<br>"),array("\n"),stripslashes($bill_data['description']))),200);
							$main_line_info = (trim($main_line_info)!=NULL?"\n".$main_line_info:NULL);

							$sub_line_info .= "\nUnit: ".trim($main_line_info)."\nQuantity: ".$bill_data['quantity'];

							if($sub_code_override!=NULL) {
								$sub_code = $sub_code_override;
							} else {
								$sub_code = $this->config->code_default;
							}
							$qty_factor = 1/$sa['quantity'];
							$line_item[] = "
							<LineItem>
							".($sub_product['sku']!=NULL&&$sub_xero_ignore['value']!=1?"<ItemCode>".$sub_product['sku']."</ItemCode>":NULL)."
							<Description>".htmlentities($sub_line_info)."</Description>
							<Quantity>".($sa['quantity']*$bill_data['quantity'])."</Quantity>
							<UnitAmount>".($bill_data['price']*$qty_factor)."</UnitAmount>
							<AccountCode>".$sub_code."</AccountCode>
							<DiscountRate>0.00</DiscountRate>
						  </LineItem>
						  ";
							$skip_line = true;
						}
					}
				}

				//-- CUSTOM OVERRIDE FOR XERO EXPORT
				if(!$skip_line) {
					$custom = unserialize($bill_data['custom']);
					if(isset($custom['switch'])) {
						$code_override = 300;
					}

					if($bill_data['object'] == 'ticket'){
						$event_ticket_data = $class_book->event_ticket_data(['id'=>$bill_data['object_id']]);
						$event_ticket_data = $class_book->event_ticket_type_data(['id'=>$event_ticket_data['ticket_type_id']]);
						if($event_ticket_data['accounting_code'] != NULL && $event_ticket_data['accounting_code'] != ""){
							$code_override = $event_ticket_data['accounting_code'];
						}
					}
					if($bill_data['object'] == 'ticket_temp'){
						$event_ticket_data = $class_book->event_ticket_temp_data(['id'=>$bill_data['object_id']]);
						//print_r($event_ticket_data);exit;
						$event_ticket_data = $class_book->event_date_ticket_data(['id'=>$event_ticket_data['event_ticket_id']]);
						$event_ticket_data = $class_book->event_ticket_type_data(['id'=>$event_ticket_data['ticket_type_id']]);

						if($event_ticket_data['accounting_code'] != NULL && $event_ticket_data['accounting_code'] != ""){
							$code_override = $event_ticket_data['accounting_code'];
						}
					}

					if($code_override!=NULL) {
						$code = $code_override;
					} else {
						$code = $this->config->code_default;
					}

					//-- CUSTOM OVERRIDE FOR XERO Tracking Categories
					$tracking_html ='<Tracking>';
					if($event_ticket_data['accounting_tracking_branch'] != NULL && $event_ticket_data['accounting_tracking_branch'] != ''){
						$tracking_html .='<TrackingCategory>
										  <Name>Branch</Name>
										  <Option>'.$event_ticket_data['accounting_tracking_branch'].'</Option>
										</TrackingCategory>';
					}
					if($event_ticket_data['accounting_tracking_month'] != NULL && $event_ticket_data['accounting_tracking_month'] != ''){
						$tracking_html .='<TrackingCategory>
										  <Name>Month</Name>
										  <Option>'.$event_ticket_data['accounting_tracking_month'].'</Option>
										</TrackingCategory>';
					}
					$tracking_html .= '</Tracking>';


					$line_info = zulu::shorten(strip_tags(str_replace(array("<br>"),array("\n"),stripslashes($bill_data['description']))),200);
					$line_info = (trim($line_info)!=NULL?"\n".$line_info:NULL);

					$line_item[] = "
					<LineItem>
						".($product_data['sku']!=NULL&&$product_meta['xero_ignore']!=1?"<ItemCode>".$product_data['sku']."</ItemCode>":NULL)."
						<Description>".htmlentities($line_info)."</Description>
						<Quantity>".$bill_data['quantity']."</Quantity>
						<UnitAmount>".$bill_data['price']."</UnitAmount>
						<AccountCode>".$code."</AccountCode>
						<DiscountRate>".($bill_data['discount']/($bill_data['quantity']*$bill_data['price'])*100)."</DiscountRate>
						".$tracking_html."
					  </LineItem>
					  ";
					  $invoice_line_total += ($bill_data['quantity']*$bill_data['price']);
				}
				unset($skip_line);
			}

			//Invoice Data
			$sale_data = $class_sale->sale_data(array('id'=>$id));
			$sale_meta = $class_sale->sale_meta($id);
			$sale_line = $class_sale->sale_line($id);
			$user_data = $class_client->client_data(array('id'=>$sale_data['client_id']));
			$invoice_data = $class_sale->sale_data(array('id'=>$id));

			$date = new DateTime;
			$date->setTimestamp($invoice_data['date']+(24*3600));

			$date_due = new DateTime;
			$date_due->setTimestamp($invoice_data['date_due']+(24*3600));

			//Client Info
			$client_data = $class_client->client_data(['id'=>$invoice_data['client_id']]);
			$client_custom_invoice_reference = $class_client->client_meta($client_data['id'],'custom_invoice_reference');

			if($client_data['company'] != '' && $client_data['company'] != NULL){
				$sale_name = $client_data['name'].' ('.$client_data['company'].')';
			}else{
				$sale_name = $client_data['name'];
			}

			if(trim($client_custom_invoice_reference['value'])!=NULL){
				$custom_invoice_reference = $client_custom_invoice_reference['value'];
			}

			if($client_data['xero_id']!=NULL) {
				$contact_info = "<ContactID>".$client_data['xero_id']."</ContactID>";
			} else {
				$contact_info = "<Name>".htmlentities(stripslashes($invoice_data['name']))."</Name>";
				if($invoice_data['email']!=NULL&&filter_var($invoice_data['email'],FILTER_VALIDATE_EMAIL)) {
					$contact_info .= "<EmailAddress>".htmlentities(stripslashes($invoice_data['email']))."</EmailAddress>";
				}
			}

			if(trim($sale_meta['xero_link']['value'])!=NULL&&!$config['update']&&!$config['void']) {
				$skip = true;
			}
			$status = ($config['Status']!=NULL?$config['Status']:'DRAFT');

			//-- Skip $0.00 invoices
			if($invoice_line_total<=0) {
				//$log[] = "Sale #".$invoice_data['reference']." ".$reference_label." ".(isset($custom_invoice_reference)?$sale_name:'')." - Export skipped, value was $0.";
				//continue; ### DISABLED - No need?
			}
			//Invoice Row
			if(!$skip) {
				if($config['update']) {
					$invoice_row[] = "
					<Invoice>
						".($config['update']?"<InvoiceID>".$sale_meta['xero_link']['value']."</InvoiceID>":NULL)."
						<Contact>
							{$contact_info}
						</Contact>
						<Date>".$date->format(DateTime::ISO8601)."</Date>
						<DueDate>".$date_due->format(DateTime::ISO8601)."</DueDate>
						<Reference>Sale #".$invoice_data['reference']." ".$reference_label." ".(isset($custom_invoice_reference)?$sale_name:'')."</Reference>
						<LineAmountTypes>".$this->config->tax."</LineAmountTypes>
						<LineItems>
						  ".implode("\n",$line_item)."
						</LineItems>
					  </Invoice>
							  ";
				} elseif($config['void']) {
					$get_invoice = $this->get_invoice($sale_meta['xero_link']['value']);
					if($get_invoice['invoice']['Status']=='AUTHORISED') {
						$void_status = 'VOIDED';
					} else {
						$void_status = 'DELETED';
					}
					if($get_invoice['invoice']['Status']=='VOIDED'||$get_invoice['invoice']['Status']=='DELETED') {
						//--skip
					} else {
						$invoice_row[] = "
					<Invoice>
						<InvoiceID>".$sale_meta['xero_link']['value']."</InvoiceID>
						<Status>".$void_status."</Status>
					  </Invoice>
							  ";
					}
				} else {
					$invoice_row[] = "
					<Invoice>
						<Type>ACCREC</Type>
						<Contact>
							{$contact_info}
						</Contact>
						<Date>".$date->format(DateTime::ISO8601)."</Date>
						<DueDate>".$date_due->format(DateTime::ISO8601)."</DueDate>
						<Reference>Sale #".$invoice_data['reference']." ".$reference_label." ".(isset($custom_invoice_reference)?$sale_name:'')."</Reference>
						<LineAmountTypes>".$this->config->tax."</LineAmountTypes>
						<Status>".$status."</Status>
						<LineItems>
						  ".implode("\n",$line_item)."
						</LineItems>
					  </Invoice>
							  ";
				}

					  $log[] = "Sale #".$invoice_data['reference']." ".$reference_label." ".(isset($custom_invoice_reference)?$sale_name:'')." - ".$status." - Exported OK";
			} else {
					  $log[] = "Sale #".$invoice_data['reference']." ".$reference_label." ".(isset($custom_invoice_reference)?$sale_name:'')." - Export skipped, duplicate entry. <a href=\"".$this->zulu->link_page('sale',array('query'=>array('Action'=>'DumpXeroMeta','id'=>$id)))."\" class=\"confirm\">DELETE</a>";
			}
			 $invoice_id[] = $invoice_data['id'];
			 $skip = false;
		}

		//Compile Final XML
		 if(count($invoice_row)>0) {
			$xml = "<Invoices>
			".implode("\n",$invoice_row)."
			</Invoices>";
		 }
		return array('xml'=>$xml,'count'=>count($invoice_row),'sale_id_array'=>$invoice_id,'log'=>$log);
	}

	//Void Sale
	function invoice_void($sale_id_arr) {
		global $zulu;

		foreach($sale_id_arr as $sale_id) {
			$xero_link = $zulu->meta_value('sale',$sale_id,'xero_link');
			if($xero_link['value']!=NULL) {
				$sale_xml  = $this->invoice_build([$sale_id],['void'=>true]);
				if($sale_xml['count']>0) {
					$response = $this->invoice_run($sale_xml['xml'],$sale_id);
					 return ['success'=>true,'message'=>$response['message']];
				 } else {
					 return ['success'=>false,'message'=>'No data to export.'];
				 }
			} else {
				return ['success'=>false,'message'=>'Item not exported to Xero yet.'];
			}
		}
	}

	//Update Sale
	function invoice_update($sale_id_arr) {
		global $zulu;

		foreach($sale_id_arr as $sale_id) {
			$xero_link = $zulu->meta_value('sale',$sale_id,'xero_link');
			if($xero_link['value']!=NULL) {
				$sale_xml  = $this->invoice_build([$sale_id],['update'=>true]);
				if($sale_xml['count']>0) {
					$response = $this->invoice_run($sale_xml['xml'],$sale_id);
					 return ['success'=>true,'message'=>$response['message']];
				 } else {
					 return ['success'=>false,'message'=>'No data to export.'];
				 }
			} else {
				return ['success'=>false,'message'=>'Item not exported to Xero yet.'];
			}
		}
	}

	//Process Sale Invoice Upload
	function invoice_run($xml,$invoice_id,$config=[]) {
		global $zulu,$XeroOAuth,$oauthSession;

		$response = $XeroOAuth->request('POST', $XeroOAuth->url('Invoices', 'core'), array(), $xml);

		if ($XeroOAuth->response['code'] == 200) {
			$invoice = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);

			$i = 0;
			foreach($invoice_id as $inv_id) {
				$zulu->meta_update('sale',$inv_id,'xero_link',$invoice->Invoices->Invoice[$i]->InvoiceID);
				$i++;
			}
			return array("success"=>true,"message"=>"Invoice exported to Xero.");
		} else {
			if(DEV_mode) {
				outputError($XeroOAuth);
				exit;
			}
			$this_resp = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
			return array("success"=>false,"message"=>"Error from Xero: ".$this_resp->Elements->DataContractBase->ValidationErrors->ValidationError->Message[0],'response'=>$response);
		}
	}

	function get_invoice($xero_invoice_id,$config=[]) {
		global $zulu,$XeroOAuth;

		if(is_array($xero_invoice_id)) {
			if($xero_invoice_id['Reference']!=NULL) {
				$filter_where = 'Reference=="'.$xero_invoice_id['Reference'].'"';
			}
			if($xero_invoice_id['InvoiceNumber']!=NULL) {
				$filter_where = 'InvoiceNumber=="'.$xero_invoice_id['InvoiceNumber'].'"';
			}
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices'.$field_get, 'core'), ['where'=>$filter_where]);
		} else {
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices/'.$xero_invoice_id.$field_get, 'core'), array());
		}

		if ($XeroOAuth->response['code'] == 200) {
			$invoice_xml = simplexml_load_string($response['response']) or die("Error: Cannot create object");
			$invoice = $invoice_xml->Invoices->Invoice;
			$invoice = (array)$invoice;
			return array("success"=>true,'invoice'=>$invoice);
		} else {
			//outputError($XeroOAuth);
			return array("success"=>false,"message"=>$XeroOAuth->response['response']);
		}
	}

	function get_invoice_public_url($xero_invoice_id) {
		global $zulu,$XeroOAuth;

		if(is_array($xero_invoice_id)) {
			if($xero_invoice_id['reference']!=NULL) {
				$response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices/'.$xero_invoice_id.'/OnlineInvoice', 'core'), array('Reference'=>$xero_invoice_id['reference']));
			}
		} else {
			$response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices/'.$xero_invoice_id.'/OnlineInvoice', 'core'), array());
		}
		if ($XeroOAuth->response['code'] == 200) {
			$online_invoice_xml = simplexml_load_string($response['response']) or die("Error: Cannot create object");
			$online_invoice = $online_invoice_xml->OnlineInvoices->OnlineInvoice;
			$online_invoice = (array)$online_invoice;
			$online_invoice_url = $online_invoice['OnlineInvoiceUrl'];
			return array("success"=>true,'invoice_url'=>$online_invoice_url);
		} else {
			//outputError($XeroOAuth);
			return array("success"=>false,"message"=>"Error finding invoice");
		}
	}

	function get_inventory() {
		global $zulu,$XeroOAuth;

		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Items', 'core'), array());
		if ($XeroOAuth->response['code'] == 200) {
			$item_xml;
			$item_xml = simplexml_load_string($response['response']) or die("Error: Cannot create object");
			return array("success"=>true,'item_xml'=>$item_xml->Items->Item,'raw'=>$item_xml);
		} else {
			//outputError($XeroOAuth);
			return array("success"=>false,"message"=>"Error finding tracking categories");
		}
	}

	function get_tracking_categories($xero_invoice_id) {
		global $zulu,$XeroOAuth;

		$response = $XeroOAuth->request('GET', $XeroOAuth->url('TrackingCategories', 'core'), array());
		if ($XeroOAuth->response['code'] == 200) {
			$tracking_categories_return;
			$tracking_categories_xml = simplexml_load_string($response['response']) or die("Error: Cannot create object");
			$tracking_categories = $tracking_categories_xml->TrackingCategories;
			$tracking_categories_array = json_decode(json_encode((array)$tracking_categories), TRUE);
			foreach($tracking_categories_array['TrackingCategory'] as $cat){
				foreach($cat['Options']['Option'] as $cat_op){
					if($cat_op['Status'] == 'ACTIVE'){
						$category[$cat['Name']][] = $cat_op['Name'];
					}
				}
			}
			$tracking_categories_return = $category;
			return array("success"=>true,'tracking_categories'=>$tracking_categories_return);
		} else {
			//outputError($XeroOAuth);
			return array("success"=>false,"message"=>"Error finding tracking categories");
		}
	}
	function date($str) {
		$date = strtotime($str);
		return date("d/m/Y",$date);
	}

	//** Default API Functions **//
	function login_html() {
		global $zulu;

		if (isset($_SESSION['access_token']) || XRO_APP_TYPE == 'Private')
		if (XRO_APP_TYPE == 'Partner')   echo '<li><a href="?refresh=1">Refresh access token</a></li>';
		if (XRO_APP_TYPE !== 'Private' && $this->vars->connected) {
			$html = '<a class="btn btn-danger" href="'.$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'xero_wipe'],'filter'=>['oauth_token','oauth_verifier']]).'"><i class="fas fa-link"></i> Remove Connection</a>';
		} elseif(XRO_APP_TYPE !== 'Private') {
			$html = '<p>You can link <b>Xero</b> with your account to easily export sale invoices!</p><p><a href="'.$zulu->link_page(PAGE_file,['query'=>['Do'=>'auth','Tab'=>'xero']]).'" class=\"bt-xro-connect\"><button class="btn btn-info" type="button"><span class="fas fa-link"></span> Connect your Xero Account</button></a></p>';
		//	echo '<li><a href="?authenticate=2"><span class=\"fas fa-link\"></span> Authenticate with Payroll API support (Australia & US organisations only)</a></li>';
		}

		return $html;
	}


}
