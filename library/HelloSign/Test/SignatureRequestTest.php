<?php

namespace HelloSign\Test;

use HelloSign\SignatureRequest;
use HelloSign\Signer;
use HelloSign\Error;

class SignatureRequestTest extends AbstractTest
{
    /**
     * @expectedException HelloSign\Error
     * @expectedExceptionMessage File does not exist
     * @group create
     */
    public function testSendSignatureRequestWithInvalidFile()
    {
        $request = new SignatureRequest;
        $request->addFile(__DIR__ . '/file_does_not_exist.docx');
    }

    /**
     * @group create
     */
    public function testSendSignatureRequest()
    {
        // Enable Test Mode
        $request = new SignatureRequest;
        $request->enableTestMode();

        // Set Request Param Signature Request
        $request->setTitle("NDA with Acme Co.");
        $request->setSubject("The NDA we talked about");
        $request->setMessage("Please sign this NDA and then we can discuss more. Let me know if you have any questions.");
        $request->addSigner("jack@example.com", "Jack");
        $request->addSigner(new Signer(array(
            'name'          => "Jill",
            'email_address' => "jill@example.com"
        )));
        $request->addCC("lawyer@example.com");
        $request->addFile(__DIR__ . '/nda.docx');

        // Send Signature Request
        $response = $this->client->sendSignatureRequest($request);


        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $this->assertEquals($request, $response);
        $this->assertEquals($response->getTitle(), $response->title);

        return $response->getId();
    }
    
	/**
     * @group create
     */
    public function testSendSignatureRequestWithFormFields()
    {
        // Enable Test Mode
        $request = new SignatureRequest;
        $request->enableTestMode();

        // Set Request Param Signature Request
        $request->setTitle("NDA with Acme Co.");
        $request->setSubject("The NDA we talked about");
        $request->setMessage("Please sign this NDA and then we can discuss more. Let me know if you have any questions.");
        $request->addSigner("jack_form@example.com", "Jack Form");
        $request->addSigner(new Signer(array(
            'name'          => "Jill Form",
            'email_address' => "jill_form@example.com"
        )));
        $request->addCC("lawyer@example.com");
        $request->addFile(__DIR__ . '/nda.docx');
        $random_prefix = 'tests' . rand(1,10000);
        $request->setFormFieldsPerDocument( 
	        array( //everything
	        	array( //document 1
	        		array( //component 1
	        			"api_id"=> $random_prefix . "_1",
						"name"=> "",
						"type"=> "text",
						"x"=> 112,
						"y"=> 328,
						"width"=> 100,
						"height"=> 16,
						"required"=> true,
						"signer"=> 0
	        		),
	        		array( //component 2
	        			"api_id"=> $random_prefix . "_2",
						"name"=> "",
						"type"=> "signature",
						"x"=> 530,
						"y"=> 415,
						"width"=> 150,
						"height"=> 30,
						"required"=> true,
						"signer"=> 1
	        		),
	        	),
	        ));

        // Send Signature Request
        $response = $this->client->sendSignatureRequest($request);


        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $this->assertEquals($request, $response);
        $this->assertEquals($response->getTitle(), $response->title);

        return $response->getId();
    }
    
    /**
     * @group create
     */
	public function testSendSignatureRequestWithTextTags()
    {
        // Enable Test Mode
        $request = new SignatureRequest;
        $request->enableTestMode();

        // Set Request Param Signature Request
        $request->setTitle("NDA with Acme Co.");
        $request->setSubject("The NDA we talked about");
        $request->setMessage("Please sign this NDA and then we can discuss more. Let me know if you have any questions.");
        $request->addSigner("jack@example.com", "Jack");
        $request->addSigner(new Signer(array(
            'name'          => "Jill",
            'email_address' => "jill@example.com"
        )));
        $request->addCC("lawyer@example.com");
        $request->addFile(__DIR__ . '/omega-multi.pdf');
        $request->setUseTextTags(true);
        $request->setHideTextTags(true);

        // Send Signature Request
        $response = $this->client->sendSignatureRequest($request);


        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $this->assertEquals($request, $response);
        $this->assertEquals($response->getTitle(), $response->title);

        return $response->getId();
    }

    /**
     * @depends testSendSignatureRequest
     * @group read
     */
    public function testGetSignatureRequests($id)
    {
        $signature_requests = $this->client->getSignatureRequests();
        $signature_request = $signature_requests[0];

        $signature_request2 = $this->client->getSignatureRequest($signature_request->getId());


        $this->assertInstanceOf('HelloSign\SignatureRequestList', $signature_requests);
        $this->assertGreaterThan(0, count($signature_requests));

        $this->assertInstanceOf('HelloSign\SignatureRequest', $signature_request);
        $this->assertNotNull($signature_request->getId());

        $this->assertInstanceOf('HelloSign\SignatureRequest', $signature_request2);
        $this->assertNotNull($signature_request2->getId());

        $this->assertEquals($signature_request, $signature_request2);
    }

    /**
     * @depends testSendSignatureRequest
     * @group update
     */
    public function testRequestEmailReminder($id)
    {
        $signature_request = $this->client->getSignatureRequest($id);
        $signatures = $signature_request->getSignatures();
        $email = $signatures[0]->getSignerEmail();
        $response = $this->client->requestEmailReminder($signature_request->getId(), $email);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotEquals($response, $signature_request);
        $this->assertEquals($response->getId(), $signature_request->getId());
    }

    /**
     * @depends testSendSignatureRequest
     * @group read
     * @group download
     */
    public function testGetFiles($id)
    {
        try {
            $response = $this->client->getFiles($id);
            $this->assertNotNull($response);
        }
        catch (Error $e) {
            $this->assertEquals(
                $e->getMessage(),
                'Unknown error. Please contact support@hellosign.com'
            );
        }
    }

    /**
     * @depends testSendSignatureRequest
     * @group destroy
     */
    public function testCancelSignatureRequest($id)
    {
        $response = $this->client->cancelSignatureRequest($id);

        $this->assertTrue($response);
    }

}