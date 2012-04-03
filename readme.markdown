#CurdBeePHP

##About CurdBee

In their own words: 

_CurdBee is a simple web application that makes billing a breeze. Use it to send estimates and invoices, track time and expenses, and accept online payments. Say goodbye to paperwork!_

You can get more info on the CurdBee site at http://www.curdbee.com/

##About CurdBeePHP

CurdBeePHP is a simple wrapper for the CurdBee API written in PHP. It is fairly self contained and only requires base PHP5 functionality (like json_encode) and PHP sockets with SSL, all of which you should find on most any PHP host. You can use CurdBeePHP to perform any client, invoice, and payment actions currently supported by the CurdBee API. Recurring profiles, estimates, and items aren't supported yet, but if you need them let me know and I'll look at implementing them (or better, submit a patch that does the job and I'll merge it in).

CurdBeePHP is not in any way associated with CurdBee. I started using them for my online invoicing and I wanted a way to tie it in with a few other services. There didn't seem to be a PHP wrapper available, so I whipped one up.

I offer no support or warranty with this code. If while using it all your data goes away, I take no responsibility. I also can't help you implement it / debug your code. My apologies in advance, I'm simply short on time. On the plus side I've tried to document it reasonably well both here and in the code, so you shouldn't have any problems figuring it out. 

You can email me at travis@ravis.org regarding bugs (please try and ensure it's a bug in CurdBeePHP and not in your code before contacting me) or patches and I'll reply / fix / merge as my time allows.

##General Principles

There are a few things that all function in CurdBeePHP do the same, and it's easier to lay it out here once than re-state it in the docs for every function.

Where this document doesn't cover something (required fields, what each field does, etc), refer to the CurdBee API documentation at http://curdbee.com/api/. To keep things simple / consistant I've tried to keep the function names and  field names the same between the CurdBeePHP data objects and the CurdBee API.

###Return Values

All the functions will return the appropriate object (if the API returns data) or TRUE for functions like DeleteClient that don't return data. 

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

Each data object has required variables, optional variables, and read only variables. Required variables must be set when creating a new record or you'll get errors back from the API. Optional may be set, if they aren't they'll either be blank or will inherit your CurdBee default settings , depending on the field. Read only variables are variables that will not be saved and are populated by the data sent back from the CurdBee API.

###Listing Data

When you request a list of items from the API (vs a single record), for example a list of clients, CurdBeePHP will return an associative array of the appropriate data object (in this example, CurdBeeClient). The keys of the array will be the CurdBee ID fields, in this example, the CurdBee client IDs. This makes it easy to find a particular record in the result set without traversing the entire array. Of course you can still look through the entire result set using foreach if you want.

###Record IDs

Please be aware that CurdBee's IDs are likely not your IDs. CurdBee IDs are assigned when you create an object (client/invoice/payment). If you maintain separate client lists, the CurdBee client ID will not match your ID, so you'll need to store it somewhere. You can always get the CurdBee ID from the returned object after you call Create (CreateClient, CreateInvoice, CreatePayment). 

For example:

	$client = $curdbee->CreateClient($client);
	$curdBeeClientId = $client->id;

## Getting Started

Here's a quick example for creating a client. You can use this example with all the other function calls as well, substituting the appropriate data object, variables, and function calls as documented below.

###Creating a CurdBeePHP Object

So let's get started using CurdBeePHP to access the CurdBee API...

The first thing you'll need to do is create a new CurdBee object, you'll use this object to interact with the CurdBee API. When creating the object you must pass your subdomain and API Token. Your subdomain is how you normally access CurdBee. For example, if you access your CurdBee admin area via https://mycompany.curdbee.com/ then your subdomain will be "mycompany". Your API Token can be retrieved from the "Your Profile" page on CurdBee once you've logged in.

If you access your admin area via a CNAME (eg: billing.mycompany.com) you'll need to find out the actual curdbee subdomain and use that instead.

	$curdbee = new CurdBee('mycompany','abc123');

Once you have your CurdBee object you can start performing API calls with it. 

###Creating a New Client

To create a new client you need to create a new CurdBeeClient object and populate it with data. You can then pass that object to the main CurdBee object, which will pass it on to the CurdBee servers. Name is the only required field (at the moment) but you can optionally populate any of the other fields as well. Any fields left blank (or set to null) will be left blank or inherit the defaults in your CurdBee account settings (eg: currency_id).

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

### Client Object

### Invoice Object

### Invoice Line Object

### Payment Object

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

### Payment Functions

CurdBee payments are always attached to an invoice, so all payment functions require a CurdBee invoice ID. Note this isn't your **invoice number** (a string, possibly numeric, which you select) but the **invoice ID** (an integer automatically assigned by CurdBee when an invoice is created).

#### Creating a Payment

#### Updating an Existing Payment

#### Deleting a Payment

