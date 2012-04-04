<?php

/**
 * A client class to easily interact with the CurdBee API.
 */
class CurdBee {

	private $Version = "0.2";

	protected $Subdomain = null;

	protected $APIToken = null;

	public $LastRequest = null;
	public $LastResponse = null;
	public $LastHTTPStatus = null;
	public $LastHTTPMessage = null;

	/**
	 * @param string $Subdomain This is the subdomain you selected when 
	 * creating your CurdBee account. For example, if you access CurdBee 
	 * via https://mycompany.curdbee.com/ then your subdomain will be 
	 * "mycompany". 
	 * @param string $APIToken This is your CurdBee API Token (check 
	 * your profile page on CurdBee to get this). 
	 */	
	public function __construct($Subdomain,$APIToken) {
		$this->Subdomain = $Subdomain;
		$this->APIToken = $APIToken;
	}

	/**
	 * Return all clients associated with the user's account.
	 * @param integer $Page The page number to retrieve, default 1
	 * @param integer $PerPage The number of clients to return per page, default 10
	 * @param array $Filters Any supported key/value pairs to use to filter the clients.
	 * As of this writing, no filters are supported.
	 * @return array An array of CurdBeeClient objects, keyed by CurdBee's Client ID
	 */
	public function ListClients($Page=1,$PerPage=10,$Filters=null) {
		$url = '/clients.json?per_page='.urlencode($PerPage).'&page='.urlencode($Page);
		return $this->IndexBy('id',$this->GET($url),'client','CurdBeeClient');
	}

	/**
	 * Return a single client in the user's account.
	 * @param integer $ClientID The CurdBee Client ID
	 * @return CurdBeeClient The corrosponding CurdBeeClient object.
	 */
	public function ShowClient($ClientID) {
		$url = '/clients/'.$ClientID.'.json';
		return $this->ConvertTo($this->GET($url),'CurdBeeClient','client');
	}

	/**
	 * Create (add) a new client
	 * @param CurdBeeClient $Client a CurdBeeClient object containing all 
	 * the client data you wish to add (as of this writing the only required
	 * field is "name").
	 * @return CurdBeeClient The newly created client object (should be very
	 * similar to the object you passed, but with additional fields, like "id").
	 */
	public function CreateClient(CurdBeeClient $Client) {
		$url = '/clients.json';
		$data = json_encode(array('client'=>$Client->PrepareData()));
		$Client = $this->ConvertTo($this->POST($url,$data),'CurdBeeClient','client');
		return $Client;
	}

	/**
	 * Update an existing client
	 * @param integer $ClientID The CurdBee client ID to update
	 * @param CurdBeeClient $Client A CurdBeeClient object containing the 
	 * information to update (null values will be ignored).
	 * @return CurdBeeClient The updated client.
	 */
	public function UpdateClient($ClientID,CurdBeeClient $Client) {
		$url = '/clients/'.$ClientID.'.json';
		$data = json_encode(array('client'=>$Client->PrepareData()));
		$Client = $this->ConvertTo($this->PUT($url,$data),'CurdBeeClient','client');
		return $Client;
	}

	/**
	 * Delete the specified client
	 * @param integer $ClientID The CurdBee client ID to delete
	 * @return boolean True
	 */
	public function DeleteClient($ClientID) {
		$url = '/clients/'.$ClientID.'.json';
		return $this->DELETE($url);
	}

	/**
	 * Return all invoices associated with the user's account.
	 * @param integer $Page The page number to retrieve, default 1
	 * @param integer $PerPage The number of invoices to return per page, default 10
	 * @param array $Filters Any supported key/value pairs to use to filter the invoices.
	 * As of this writing, only "client" is supported (with the client ID to filter by).
	 * @return array An array of CurdBeeClient objects, keyed by CurdBee's Client ID
	 */
	public function ListInvoices($Page=1,$PerPage=10,$Filters=null) {
		$url = '/invoices.json?per_page='.urlencode($PerPage).'&page='.urlencode($Page);
		if ($Filters) {
			foreach ($Filters as $FilterKey=>$FilterValue) {
				$url .= '&'.urlencode($FilterKey).'='.urlencode($FilterValue);
			}
		}
		$invoices = $this->IndexBy('id',$this->GET($url),'invoice','CurdBeeInvoice');
		foreach ($invoices as &$invoice) {
			$invoice->line_items = $this->IndexBy('id',$invoice->line_items,null,'CurdBeeInvoiceLine');
			$invoice->client = $this->ConvertTo($invoice->client,'CurdBeeClient');
		}
		return $invoices;
	}

	/**
	 * Return a single invoice in the user's account.
	 * @param integer $InvoiceID The CurdBee Invoice ID (not YOUR invoice ID)
	 * @return CurdBeeInvoice The corrosponding CurdBeeInvoice object.
	 */
	public function ShowInvoice($InvoiceId) {
		$url = '/invoices/'.$InvoiceId.'.json';
		$invoice = $this->ConvertTo($this->GET($url),'CurdBeeInvoice','invoice');
		$invoice->line_items = $this->IndexBy('id',$invoice->line_items,null,'CurdBeeInvoiceLine');
		$invoice->client = $this->ConvertTo($invoice->client,'CurdBeeClient');
		return $invoice;
	}

	/**
	 * Create a new invoice
	 * @param CurdBeeInvoice The completed CurdBeeInvoice object you want to add.
	 * @return CurdBeeInvoice The returned CurdBeeInvoice object (should be the same as the
	 * object you send, but with additional fields for the CurdBee Invoice ID and other
	 * fields).
	 */
	public function CreateInvoice(CurdBeeInvoice $Invoice) {
		$url = '/invoices.json';
		$data = json_encode(array('invoice'=>$Invoice->PrepareData()));
		$invoice = $this->ConvertTo($this->POST($url,$data),'CurdBeeInvoice','invoice');
		$invoice->line_items = $this->IndexBy('id',$invoice->line_items,null,'CurdBeeInvoiceLine');
		$invoice->client = $this->ConvertTo($invoice->client,'CurdBeeClient');
		return $invoice;
	}

	/**
	 * Update an existing invoice
	 * @param integer $InvoiceID The CurdBee invoice ID for the invoice you want to update.
	 * @param CurdBeeInvoice The CurdBeeInvoice object containing the data you wish to 
	 * update with.
	 * @return CurdBeeInvoice The CurdBeeInvoice object returned (should be the same as the 
	 * object you passed, with additional fields like the CurdBee invoice ID).
	 */
	public function UpdateInvoice($InvoiceID,CurdBeeInvoice $Invoice) {
		$url = '/invoices/'.$InvoiceID.'.json';
		$data = json_encode(array('invoice'=>$Invoice->PrepareData()));
		$invoice = $this->ConvertTo($this->PUT($url,$data),'CurdBeeInvoice','invoice');
		$invoice->line_items = $this->IndexBy('id',$invoice->line_items,null,'CurdBeeInvoiceLine');
		$invoice->client = $this->ConvertTo($invoice->client,'CurdBeeClient');
		return $invoice;
	}

	/**
	 * Close an invoice.
	 * @param integer $InvoiceID The CurdBee invoice ID to close.
	 * @return boolean TRUE
	 */
	public function CloseInvoice($InvoiceID) {
		$url = '/invoices/'.$InvoiceID.'/close.json';
		return $this->POST($url);
	}

	/**
	 * Reopen a closed invoice.
	 * @param integer $InvoiceID The CurdBee invoice ID to reopen.
	 * @return boolean TRUE
	 */
	public function ReopenInvoice($InvoiceID) {
		$url = '/invoices/'.$InvoiceID.'/reopen.json';
		return $this->POST($url);
	}

	/**
	 * Delivery (eg: email) an invoice.
	 * @param integer $InvoiceID The CurdBee invoice ID to deliver.
	 * @param mixed $Recipients The email address(es) to deliver to. Can either be a
	 * string (a single address) or an array of strings (multiple addresses). No validation
	 * is performed on the address to make sure it's valid.
	 * @param mixed $BlindCopy The email address(es) to BCC in the email. Can either be a
	 * string (a single address) or an array of strings (multiple addresses). No validation
	 * is performed on the address to make sure it's valid.
	 * @return boolean TRUE
	 */
	public function DeliverInvoice($InvoiceID,$Recipients,$BlindCopy=null) {
		$url = '/deliver/invoice/'.$InvoiceID.'.json';
		if (!is_array($Recipients)) {
			$Recipients = array($Recipients);
		}
		else {
			// re-key the array (if it's an associative array, de-associate it)
			$Recipients = array_values($Recipients);
		}
		$data = array(
			'delivery'=>array(
				'recipients'=>$Recipients
			)
		);
		if ($BlindCopy) {
			if (!is_array($BlindCopy)) {
				$BlindCopy = array($BlindCopy);
			}
			else {
				// re-key the array (if it's an associative array, de-associate it)
				$BlindCopy = array_values($BlindCopy);
			}
			$data['delivery']['blind_copy'] = $BlindCopy;
		}
		$data = json_encode($data);
		return $this->POST($url,$data);
	}

	/**
	 * Deletes and existing invoice
	 * @param integer $InvoiceID The CurdBee invoice ID you want to delete.
	 * @return boolean True
	 */
	public function DeleteInvoice($InvoiceID) {
		$url = '/invoices/'.$InvoiceID.'.json';
		return $this->DELETE($url);
	}

	/**
	 * Fetch a list of currencies supported by CurdBee (and their IDs)
	 * @return array An array (indexed with the CurdBee currency IDs) containing 
	 * currency information.
	 */
	public function ListCurrencies() {
		$url = '/default_settings/currencies.json';
		return $this->IndexBy('id',$this->GET($url),'currency');
	}

	/**
	 * Lists payments associated with the specified CurdBee invoice ID.
	 * @param integer $InvoiceID The invoice ID you would like to list payments for.
	 * This is CurdBee's invoice ID, not your company's invoice ID.
	 * @return array An array (indexed with CurdBee payment IDs) of CurdBeePayment 
	 * objects.
	 */
	public function ListPayments($InvoiceID) {
		$url = '/invoices/'.$InvoiceID.'/payments.json';
		$payments = $this->IndexBy('id',$this->GET($url),'payment','CurdBeePayment');
		return $payments;
	}

	/**
	 * Retrieve information for a single payment.
	 * @param integer $InvoiceID The CurdBee invoice ID the payment is associated with.
	 * @param integer $PaymentID The CurdBee payment ID you wish to retrieve.
	 * @return CurdBeePayment A CurdBeePayment object.
	 */
	public function ShowPayment($InvoiceID,$PaymentID) {
		$url = '/invoices/'.$InvoiceID.'/payments/'.$PaymentID.'.json';
		$payment = $this->ConvertTo($this->GET($url),'CurdBeePayment','payment');
		return $payment;
	}

	/**
	 * Create (add) a payment to an invoice.
	 * @param integer $InvoiceID The CurdBee invoice ID you wish to attach the payment 
	 * to (note this is the CurdBee invoice ID, not your company's invoice ID).
	 * @param CurdBeePayment $Payment The CurdBeePayment object containing the information
	 * for the new invoice.
	 * @return CurdBeePayment A CurdBeePayment object containing the payment you just
	 * added. This should closely resemble the one you passed in, except with additional
	 * fields (like the CurdBee payment ID).
	 */
	public function CreatePayment($InvoiceID,CurdBeePayment $Payment) {
		$url = '/invoices/'.$InvoiceID.'/payments.json';
		$data = json_encode(array('payment'=>$Payment->PrepareData()));
		$payment = $this->ConvertTo($this->POST($url,$data),'CurdBeePayment','payment');
		return $payment;
	}

	/**
	 * Update an existing payment
	 * @param integer $InvoiceID The CurdBee invoice ID associated with the payment you
	 * wish to update.
	 * @param integer $PaymentID The CurdBee payment ID you wish to update.
	 * @param CurdBeePayment The CurdBeePayment object containing the information you
	 * wish to update the payment with (null fields will be ignored).
	 * @return CurdBeePayment A CurdBeePayment object containing the resulting updated
	 * payment.
	 */
	public function UpdatePayment($InvoiceID,$PaymentID,CurdBeePayment $Payment) {
		$url = '/invoices/'.$InvoiceID.'/payments/'.$PaymentID.'.json';
		$data = json_encode(array('payment'=>$Payment->PrepareData()));
		$payment = $this->ConvertTo($this->PUT($url,$data),'CurdBeePayment','payment');
		return $payment;
	}

	/**
	 * Deletes a payment from an invoice
	 * @param integer $InvoiceID The CurdBee invoice ID you wish to delete (note this is
	 * the CurdBee invoice ID, not your company's invoice ID).
	 * @param integer $PaymentID The CurdBee payment ID you wish to delete.
	 * @return boolean True
	 */
	public function DeletePayment($InvoiceID,$PaymentID) {
		$url = '/invoices/'.$InvoiceID.'/payments/'.$PaymentID.'.json';
		return $this->DELETE($url);
	}





	/* **************************************** *
	 * You should not need to access anything below here unless you're developing
	 * **************************************** */




	/**
	 * Simply converts a generic PHP object into one of the CurdBeePHP data objects.
	 */
	protected function ConvertTo($Data,$Class,$Name=null) {
		if ($Name) {
			if (!isset($Data->$Name)) {
				throw new Exception("Expected $Name to exist in the data, but did not find it.");
			}
			$Data = $Data->$Name;
		}
		$obj = new $Class();
		foreach ($Data as $field=>$value) {
			$obj->$field = $value;
		}
		return $obj;
	}

	/**
	 * Indexes an array of objects into an associative array based on a field.
	 * Optionally converts the objects in that array using ConvertTo.
	 */
	protected function IndexBy($IndexField,$UnsortedData,$Name=null,$ConvertToClass=null) {
		$SortedData = array();
		foreach ($UnsortedData as $Data) {
			if ($Name) {
				if (!isset($Data->$Name)) {
					throw new Exception("Expected $Name to exist in the data, but did not find it.");
				}
				$Data = $Data->$Name;
			}
			if (!isset($Data->$IndexField)) {
				throw new Exception("Expected $IndexField to exist in the data, but did not find it.");
			}
			if ($ConvertToClass) {
				$SortedData[$Data->$IndexField] = $this->ConvertTo($Data,$ConvertToClass);
			}
			else {
				$SortedData[$Data->$IndexField] = $Data;
			}
		}
		return $SortedData;
	}

	/**
	 * Performs an HTTP GET.
	 * @param string $url The URL to GET.
	 * @return mixed JSON decided data from the server.
	 */
	protected function GET($url) {
		return $this->HTTP('GET',$url);
	}

	/**
	 * Performs an HTTP POST.
	 * @param string $url The URL to POST.
	 * @param string $data The data to POST in the body of the request.
	 * @return mixed JSON decided data from the server.
	 */
	protected function POST($url,$data=null) {
		return $this->HTTP('POST',$url,$data);
	}

	/**
	 * Performs an HTTP PUT.
	 * @param string $url The URL to PUT.
	 * @param string $data The data to PUT in the body of the request.
	 * @return mixed JSON decided data from the server.
	 */
	protected function PUT($url,$data=null) {
		return $this->HTTP('PUT',$url,$data);
	}

	/**
	 * Performs an HTTP DELETE.
	 * @param string $url The URL to DELETE.
	 * @return mixed JSON decided data from the server (usually TRUE).
	 */
	protected function DELETE($url) {
		return $this->HTTP('DELETE',$url);
	}

	/**
	 * Performs a raw HTTP request.
	 * @param string $method One of GET, POST, PUT, or DELETE.
	 * @param string $url The URL to GET, POST, PUT, or DELETE.
	 * @param string $data The content of the request body.
	 * @return mixed JSON decided data from the server.
	 */
	protected function HTTP($method,$url,$data=null) {
		// add the API Token
		if (strpos($url,'?')===false) {
			$url .= '?api_token='.urlencode($this->APIToken);
		}
		else {
			$url .= '&api_token='.urlencode($this->APIToken);
		}
		$content = $method." ".$url." HTTP/1.0\r\n";
		$content .= "Host: ".$this->Subdomain.".curdbee.com\r\n";
		$content .= "Accept: text/json,text/html,text/plain,*/*\r\n";
		$content .= "User-Agent: CurdBeePHP/".$this->Version."\r\n";
		$content .= "Content-Type: application/json\r\n"; 
		if ($data) {
			$content .= "Content-Length: ".strlen($data)."\r\n";
		}
		$content .= "\r\n";
		$content .= $data;
		$this->LastRequest = $content;
		$this->LastResponse = null;
		$this->LastHTTPStatus = null;
		$this->LastHTTPMessage = null;

		$fp = fsockopen('ssl://'.$this->Subdomain.'.curdbee.com', 443, $errno, $errstr, 180);
		if (!$fp) {
			throw new Exception('Could not connect to server ['.$errstr.']. Check that your subdomain is correct and that you have network access.');
		}

		fwrite($fp, $content, strlen($content));
		$response = '';
		while (!feof($fp)) {
			$response .= fread($fp,1024*10);
		}
		fclose($fp);
		$this->LastResponse = $response;

		// strip out headers and send back the body decoded
		if (preg_match("/^(.*?)(\r\n\r\n(.*))?$/s",$response,$temp)) {
			$headers = trim($temp[1]);
			$body = trim($temp[3]);
		}
		else {
			throw new Exception('Something very bad happened (this should never happen for HTTP protocol requests)');
		}
		$json = json_decode($body);

		// look for error statuses
		if (preg_match('/^HTTP\/\d+\.\d+ (\d+) ([^\r\n]+)/',$headers,$temp)) {
			$HTTPStatus = $temp[1];
			$HTTPMessage = $temp[2];
		}
		else {
			throw new Exception('Something else very bad happened (this should never happen for HTTP protocol requests)');
		}
		$this->LastHTTPStatus = $HTTPStatus;
		$this->LastHTTPMessage = $HTTPMessage;

		// if it's anything other than a 2xx it was an error
		if ($HTTPStatus<200 || $HTTPStatus>299) {
			// look for errors
			if ($body && is_array($json) && is_array($json[0])) {
				$error = '';
				foreach ($json as $err) {
					$error .= "$err[0] $err[1]\n";
				}
			}
			else {
				$error = $HTTPMessage;
			}
			throw new Exception($error,$HTTPStatus);
		}

		if ($body) {
			return $json;
		}

		return true;
	}

}


/**
 * A simple object that will be extended for the other CurdBeePHP data objects.
 */
class CurdBeeBase {
	protected $DataFields = array();
	/**
	 * Prepares an array to be JSON encoded.
	 */
	public function PrepareData() {
		$data = array();
		foreach ($this->DataFields as $key=>$value) {
			if (isset($this->$value) && !is_null($this->$value)) {
				$data[$key] = $this->$value;
			}
		}
		return $data;
	}

}


/**
 * A simple class to store data for a CurdBee client.
 */
class CurdBeeClient extends CurdBeeBase {
	public $name = null;
	public $email = null;
	public $currency_id = null;
	public $address = null;
	public $city = null;
	public $province = null;
	public $zip_code = null;
	public $country = null;
	public $phone = null;
	public $fax = null;
	public $custom_field_name = null;
	public $custom_field_value = null;

	// Setting these values will have no effect (they won't be saved)
	public $id = null;
	public $created_at = null;
	public $updated_at = null;
	public $send_copy = null;
	public $full_address_with_comma = null;
	public $currency = null;

	protected $DataFields = array(
		'name'=>'name',
		'email'=>'email',
		'currency_id'=>'currency_id',
		'address'=>'address',
		'city'=>'city',
		'province'=>'province',
		'zip_code'=>'zip_code',
		'country'=>'country',
		'phone'=>'phone',
		'fax'=>'fax',
		'custom_field_name'=>'custom_field_name',
		'custom_field_value'=>'custom_field_value',
	);
}


/**
 * A simple class to store data for a CurdBee invoice.
 */
class CurdBeeInvoice extends CurdBeeBase {
	/**
	 * This must be an array of CurdBeeInvoiceLine objects.
	 */
	public $line_items = array();
	public $discount = null;
	public $notes = null;
	public $date = null;
	public $shipping = null;
	public $client_id = null;
	public $due_date = null;
	public $summary = null;
	public $invoice_no = null;
	public $allow_partial_payments= null;
	public $shipping_amount = null;
	public $tax = null;
	public $tax2 = null;

	// Setting these values will have no effect (they won't be saved)
	public $id = null;
	public $created_at = null;
	public $updated_at = null;
	public $total_billed = null;
	public $total_due = null;
	public $tax2_compound = null;
	public $discount_amount = null;
	public $hash_key = null;
	public $tax_amount = null;
	public $client = null;
	public $payment_options = null;
	public $tax2_amount = null;
	public $sub_total = null;
	public $paid_total = null;
	public $state = null;

	protected $DataFields = array(
		'tax'=>'tax',
		'discount'=>'discount',
		'notes'=>'notes',
		'date'=>'date',
		'shipping'=>'shipping',
		'client_id'=>'client_id',
		'due_date'=>'due_date',
		'summary'=>'summary',
		'invoice_no'=>'invoice_no',
		'allow_partial_payments'=>'allow_partial_payments',
		'shipping_amount'=>'shipping_amount',
	);

	public function PrepareData() {
		$data = parent::PrepareData();
		$lines = array();
		foreach ($this->line_items as $line) {
			$lines[] = $line->PrepareData();
		}
		$data['line_items_attributes'] = $lines;
		return $data;
	}
}


/**
 * A simple class to store data for a CurdBee invoice line.
 */
class CurdBeeInvoiceLine extends CurdBeeBase {
	public $name_and_description = null;
	public $price = null;
	public $quantity = null;
	public $unit = null;

	// settings these values will have no effect (they won't be saved)
	public $id = null;
	public $sort_order = null;
	public $total = null;

	protected $DataFields = array(
		'name_and_description'=>'name_and_description',
		'price'=>'price',
		'quantity'=>'quantity',
		'unit'=>'unit',
	);
}


/**
 * A simple class to store data for a CurdBee payment.
 */
class CurdBeePayment extends CurdBeeBase {
	public $date = null;
	public $amount = null;
	public $payment_method = null;

	// settings these values will have no effect (they won't be saved)
	public $id = null;
	public $created_at = null;
	public $updated_at = null;
	public $balance = null;

	protected $DataFields = array(
		'date'=>'date',
		'amount'=>'amount',
		'payment_method'=>'payment_method',
	);
}

