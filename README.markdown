#CurdBeePHP

##About CurdBee

In their own words: 

_CurdBee is a simple web application that makes billing a breeze. Use it to send estimates and invoices, track time and expenses, and accept online payments. Say goodbye to paperwork!_

You can get more info from the CurdBee site at http://www.curdbee.com/

##About CurdBeePHP

CurdBeePHP is a simple wrapper for the CurdBee API written in PHP. It is fairly self contained and only requires PHP 5.2 functionality (like json_encode) and PHP sockets with SSL, all of which you should find on most any PHP host. You can use CurdBeePHP to perform any client, invoice, and payment actions currently supported by the CurdBee API. Recurring profiles, estimates, and items aren't supported yet, but if you need them let me know and I'll look at implementing them (or better, submit a patch that does the job and I'll merge it in).

CurdBeePHP is not in any way associated with CurdBee. I started using them for my online invoicing and I wanted a way to tie it in with a few other services. There didn't seem to be a PHP wrapper available, so I whipped one up.

### License

CurdBeePHP is licensed under the Modified BSD License (aka the 3 Clause BSD). Basically you can use it for any purpose, including commercial, so long as you leave the copyright notice intact and don't use my name or the names of any other contributors to promote products derived from CurdBeePHP.

	Copyright (c) 2012, Travis Richardson
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	    * Redistributions of source code must retain the above copyright
	      notice, this list of conditions and the following disclaimer.
	    * Redistributions in binary form must reproduce the above copyright
	      notice, this list of conditions and the following disclaimer in the
	      documentation and/or other materials provided with the distribution.
	    * Neither the name of the Travis Richardson nor the names of its 
	      contributors may be used to endorse or promote products derived 
	      from this software without specific prior written permission.
	
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL TRAVIS RICHARDSON BE LIABLE FOR ANY
	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

### Warranty / Support

I offer no support or warranty with this code. If while using it all your data goes away, I take no responsibility. I also can't help you implement it / debug your code. My apologies in advance, I'm simply short on time. On the plus side I've tried to document it reasonably well both here and in the code, so assuming you're familiar with PHP you shouldn't have any problems figuring it out. 

### Bugs / Patches

You can post an issue to the [CurdBeePHP issues page on GitHub](https://github.com/ravisorg/CurdBeePHP/issues) regarding bugs (please try and ensure it's a bug in CurdBeePHP and not in your code before contacting me) or patches and I'll reply / fix / merge as my time allows.

##General Principles

There are a few things that all function in CurdBeePHP do the same, and it's easier to lay it out here once than re-state it in the docs for every function.

Where this document doesn't cover something (required fields, what each field does, etc), refer to the [CurdBee API documentation](http://curdbee.com/api/). To keep things simple / consistent I've tried to keep the function names in the main CurdBee object and the field names in the CurdBeePHP data objects the same as are used in the CurdBee API.

###Return Values

All the functions will return the appropriate data object (if the API returns data) or TRUE for functions like DeleteClient that don't return data. 

###Errors

If an error occurs a standard PHP Exception will be thrown with whatever is known about the error as the exception message and the HTTP status code as the exception code. Because of this it's recommended you wrap all your API calls in try/catch statements. For example:

	try {
		$curdbee->CreateClient($client);
	}
	catch (Exception $e) {
		print "Error creating client: ".$e->getMessage()."\n";
		print "HTTP status code was: ".$e->getCode()."\n";
	}

For the examples below I'm skipping all that so it's a little easier to read.

###Data Objects

CurdBeePHP uses simple data objects to pass data into and receive data from the main CurdBeePHP object. There are data classes for clients (CurdBeeClient), invoices (CurdBeeInvoice), invoice line items (CurdBeeInvoiceLine) and payments (CurdBeePayment). 

Each data object has required variables, optional variables, and read only variables. Required variables must be set when creating a new record or you'll get errors back from the API. Optional variables may be set, if they aren't they'll either be blank or will inherit your CurdBee default settings , depending on the field. Read only variables will not be saved and are populated by the data sent back from the CurdBee API.

###Listing Data

When you request a list of items from the API (vs a single record), for example a list of clients, CurdBeePHP will return an associative array of the appropriate data object (in this example, CurdBeeClient). The keys of the array will be the CurdBee ID fields, in this example, the CurdBee client IDs. This makes it easy to find a particular record in the result set without traversing the entire array. Of course you can still look through the entire result set using foreach if you need to.

###Record IDs

Be aware that CurdBee's IDs are likely not your IDs. CurdBee IDs are assigned when you create an object (client/invoice/payment). If you maintain separate client lists, the CurdBee client ID will not match your ID, so you'll need to store it somewhere on your end (CurdBeePHP doesn't handle this). You can always get the CurdBee ID from the returned object after you call Create (CreateClient/CreateInvoice/CreatePayment). 

For example:

	$client = $curdbee->CreateClient($client);
	$curdBeeClientId = $client->id;

###Date Formating

When passing dates to and from the CurdBee API, use the format YYYY/MM/DD. For example, "2012/04/28".

## Getting Started

Here's a quick example for creating a CurdBee client. You can use this example for any of the other functions documented below, just substitute the appropriate data object / variable / API call with the action you wish to perform.

###Creating a CurdBeePHP Object

The first thing you'll need to do is create a new CurdBee object, you'll use this object to interact with the CurdBee API. When creating the object you must pass your subdomain and API Token. Your subdomain is how you normally access CurdBee. For example, if you access your CurdBee admin area via https://mycompany.curdbee.com/ then your subdomain will be "mycompany". Your API Token can be retrieved from the "Your Profile" page on CurdBee once you've logged in.

If you access your admin area via a CNAME (eg: billing.mycompany.com) you'll need to find out the actual curdbee subdomain and use that instead of your CNAME.

	$curdbee = new CurdBee('mycompany','abc123');

Once you have your CurdBee object you can start performing API calls with it. 

###Creating a New Client

To create a new client you need to create a new CurdBeeClient data object and populate it with the values you wish to save. You can then pass that data object to the main CurdBee object, which will pass it on to the CurdBee servers and get back a response. When creating a client, "name" is the only required field, but you can optionally populate any of the other fields as well. Any fields left blank (or set to null) will be left blank (eg: email) or inherit the defaults in your CurdBee account settings (eg: currency_id).

	$client = new CurdBeeClient();
	$client->name = 'Client Name';
	$client->email = 'client@company.com';
	$client->currency_id = 123; // see CurdBee::ListCurrencies() for available currencies
	$client->address = '123 My Street';
	$client->city = 'My City';
	$client->province = 'My State';
	$client->zip_code = '12345';
	$client->country = 'USA';
	$client->phone = '1 (234) 567-8901';
	$client->fax = '1 (123)456-7890';
	$client->custom_field_name = 'Client Tax Code';
	$client->custom_field_value = '1234567890';

Now that you have the CurdBeeClient object filled out you can pass it to the main CurdBee object.

	$newClient = $curdbee->CreateClient($client);
	
The returned object will have all the variables you set as well as the additional CurdBee read only values, like id, created_on, etc.

	$curdBeeClientId = $newClient->id;
	$createdTime = $newClient->created_at;
	$updatedTime = $newClient->updated_at;
	$sendCopy = $newClient->send_copy;
	$fullAddress = $newClient->full_address_with_comma;
	$currency = $newClient->currency;

## Data Object Reference

### Client Object (CurdBeeClient)

Stores data for a single client.

####Required Fields

* name _- only required when creating a client, optional when updating_

####Optional Fields

* email
* currency_id _- the CurdBee numeric currency ID (see CurdBee::ListCurrencies)_
* address
* city
* province
* zip_code
* country
* phone
* fax
* custom_field_name
* custom_field_value

####Read Only Fields

These fields will not be sent to the server, setting them will have no effect. However when CurdBeePHP passes back a CurdBeeClient object, you can read these fields to find out additional information about the client.

* id _- the CurdBee client ID_
* created_at
* updated_at
* send_copy
* full_address_with_comma
* currency

### Invoice Object

Stores data for a single invoice and it's associated invoice lines.

#### Required Fields

* client_id _- only required when creating an invoice, optional when updating_

#### Optional Fields

* line_items _- an array of CurdBeeInvoiceLine objects_
* discount
* notes
* date
* shipping
* due_date
* summary
* invoice_no _- your own internal invoice number, not to be confused with CurdBee's invoice ID_
* allow_partial_payments
* shipping_amount
* tax
* tax2

#### Read Only Fields

These fields will not be sent to the server, setting them will have no effect. However when CurdBeePHP passes back a CurdBeeInvoice object, you can read these fields to find out additional information about the invoice.

* id _- the CurdBee client ID_
* created_at
* updated_at
* total_billed
* total_due
* tax2_compound
* discount_amount
* hash_key
* tax_amount
* client _- a CurdBeeClient object with only certain fields filled in_
* payment_options
* tax2_amount
* sub_total
* paid_total
* state _- the "status" of the invoice (open, closed, etc)_

### Invoice Line Object

Stores data for a single line on an invoice. You cannot pass CurdBeeInvoiceLine objects to the API by themselves, they're always added to an invoice first.

#### Required Fields

* name_and_description
* price
* quantity
* unit _- a string describing what "1" is (eg: 'item','hour','meter',etc)_

#### Optional Fields

None

#### Read Only Fields

* id _- the CurdBee invoice line item ID_
* sort_order
* total

### Payment Object

Stores data for a single payment.

#### Required Fields

* date
* amount

#### Optional Fields

* payment_method

#### Read Only Fields

* id _- the CurdBee payment ID_
* created_at
* updated_at
* balance

## API Function Calls

These calls are performed using the main CurdBeePHP object. For the purpose of the examples, $curdbee is an instance of the CurdBee class. ie: pretend this preceeds all the examples:

	$curdbee = new CurdBee($subdomain,$apitoken);

### Client Functions

####Creating (adding) a Client

	$curdbee->CreateClient($Client);

* $Client is a CurdBeeClient object, prepopulated with the data you want to add. The only required field is "name".

CreateClient returns a single CurdBeeClient object.

####Updating an Existing Client

	$curdbee->UpdateClient($ClientID,$Client);
	
* $ClientID is the CurdBee client ID
* $Client is a CurdBeeClient object containing the data you want to change for the client. Any fields left blank or null will not be altered (ie: if you leave name null, name will remain unchanged on the server). If you wish to overwrite a field use an empty string or zero (0) for numeric fields.

UpdateClient returns a single CurdBeeClient object.

####Show Client

Retrieves a single client record.

	$curdbee->ShowClient($ClientID);

* $ClientID is the CurdBee client ID to retrieve.

ShowClient returns a single CurdBeeClient object.

####Listing Clients

List all the existing clients in your CurdBee account.

	$curdbee->ListClients($Page,$PerPage,$Filters);
	
* $Page is an optional integer describing the page number you want to retrieve (starting at 1, not zero).
* $PerPage is an optional number of records per page you wish to retrieve, defaulted to 10.
* $Filters optionally allows you to filter the results to only return matching records. At the time of this writing CurdBee does not support filtering clients, so this is simply here for future proofing.

ListClients will return an array of CurdBeeClient objects, indexed by CurdBee's client ID.

####Deleting a Client

	$curdbee->DeleteClient($ClientID);
	
* $ClientID is the CurdBee client ID

DeleteClient returns TRUE.

### Invoice Functions

#### Creating an Invoice

	$curdbee->CreateInvoice($Invoice);
	
* $Invoice is a CurdBeeInvoice object containing the invoice data you want to save. Only "client_id" is required.

CreateInvoice returns a single CurdBeeInvoice object.

#### Updating an Existing Invoice

	$curdbee->UpdateInvoice($InvoiceID,$Invoice);
	
* $InvoiceID is the CurdBee invoice ID.
* $Invoice is a CurdBeeInvoice object containing the data you wish to update. Any fields left blank or null will not be altered (ie: if you leave client_id null, client_id will remain unchanged on the server). If you wish to overwrite a field use an empty string or zero (0) for numeric fields.

#### Delivering (Emailing) an Invoice

This will "deliver" an invoice to the address(es) you specify and mark the invoice as sent.

	$curdbee->DeliverInvoice($InvoiceID,$Recipients,$BlindCopy);
	
* $InvoiceID is the CurdBee invoice ID you wish to deliver.
* $Recipients is either a string containing an email address, or an array of strings containing email addresses. These are the addresses you wish to deliver the invoice to.
* BlindCopy is similar to $Recipients (string or array of emails) except that these addresses will be BCCed instead of appearing in the TO field of the email.

DeliverInvoice returns TRUE.

#### Closing an Invoice

This will mark the invoice as "closed" in CurdBee.

	$curdbee->CloseInvoice($InvoiceID);
	
* $InvoiceID is the CurdBee invoice ID you want to close.

CloseInvoice returns TRUE.

#### Reopening an Invoice

This will mark a closed invoice as "open" again.

	$curdbee->ReopenInvoice($InvoiceID);
	
* $InvoiceID is the closed CurdBee invoice ID you wish to reopen.

ReopenInvoice returns TRUE.

#### Show an Invoice

Retrieves a single invoice from CurdBee.

	$curdbee->ShowInvoice($InvoiceID);
	
* $InvoiceID The CurdBee invoice ID you wish to retrieve.

ShowInvoice returns a single CurdBeeInvoice object.

#### List Invoices

Lists all invoices in your CurdBee account.

	$curdbee->ListInvoices($Page,$PerPage,$Filters);
	
* $Page is an optional integer specifying the page number you require (starting at 1, not zero). 
* $PerPage is the optional number of records you wish to retrieve per page, defaulting to 10.
* $Filters is an array of key/value pairs to use to filter the results. Currently the only supported filter is "client", which is a CurdBee client ID. Specifying client will only return invoices attached to that client ID.

ListInvoices returns an associative array of CurdBeeInvoice objects, with the array keys being the CurdBee invoice IDs.

#### Deleting an Invoice

	$curdbee->DeleteInvoice($InvoiceID);
	
* $InvoiceID is the CurdBee invoice ID you wish to delete.

DeleteInvoice returns TRUE.

### Payment Functions

CurdBee payments are always attached to an invoice, so all payment functions require a CurdBee invoice ID. Note this isn't your **invoice number** (a string, possibly numeric, which you select) but the **invoice ID** (an integer automatically assigned by CurdBee when an invoice is created).

#### Creating a Payment

	$curdbee->CreatePayment($InvoiceID,$Payment);
	
* $InvoiceID is the CurdBee invoice ID to apply the payment to.
* $Payment is a CurdBeePayment object containing the payment information to apply. The fields "date" and "amount" are required.

CreatePayment returns a single CurdBeePayment object.

#### Updating an Existing Payment

	$curdbee->UpdatePayment($InvoiceID,$PaymentID,$Payment);
	
* $InvoiceID is the CurdBee invoice ID to apply the payment to.
* $PaymentID is the CurdBee payment ID to update.
* $Payment is a CurdBeePayment object containing the payment information to update.

UpdatePayment returns a single CurdBeePayment object.

#### List Payments

Lists all payments that have been applied/attached to an invoice. Note there is no way to list all payments globally, you must specify an invoice ID.

	$curdbee->ListPayments($InvoiceID);
	
* $InvoiceID is the CurdBee invoice ID you wish to retrieve payments for.

ListPayments returns an associative array of CurdBeePayment objects, with the array keys being the CurdBee payment IDs.

#### Show a Payment

Retrieves a single payment from CurdBee.

	$curdbee->ShowPayment($InvoiceID,$PaymentID);
	
* $InvoiceID is the CurdBee invoice ID the payment you wish to retrieve is attached/applied to.
* $PaymentID is the CurdBee payment ID to retrieve.

ShowPayment returns a single CurdBeePayment object.

#### Deleting a Payment

	$curdbee->DeletePayment($InvoiceID,$PaymentID);
	
* $InvoiceID is the CurdBee invoice ID that the payment you want to delete is attached to.
* $PaymentID is the CurdBee payment ID to delete.

DeletePayment returns TRUE.

