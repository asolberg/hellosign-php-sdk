<?php

namespace HelloSign\Test;

use HelloSign\SignatureRequest;
use HelloSign\Template;
use HelloSign\TemplateSignatureRequest;
use HelloSign\EmbeddedSignatureRequest;

class EmbeddedSignatureRequestTest extends AbstractTest
{
    /**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequest()
    {
        // Create the signature request
        $request = new SignatureRequest;
        $request->enableTestMode();
        $request->setTitle('Embedded NDA');
        $request->addSigner('jack@example.com', 'Jack');
        $request->addFile(__DIR__ . '/nda.docx');

        // Turn it into an embedded request
        $client_id = $_ENV['HELLOSIGN_CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);

        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);


        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());


        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }
    
	/**
     * @group create
     */
    public function testCreateEmbeddedSignatureRequestWithTemplate()
    {
        // Get a template
        
    	$templates = $this->client->getTemplates();
        $template = $templates[0];
        
        // Create the signature request

        $request = new TemplateSignatureRequest;
        $request->enableTestMode();
        $request->setTemplateId($template->getId());
        $request->setSubject('Purchase Order');
        $request->setMessage('Glad we could come to an agreement.');

        foreach ($template->getSignerRoles() as $i => $role) {
            $request->setSigner($role->name, "george$i@example.com", "George {$role->name}");
        }
        foreach ($template->getCCRoles() as $i => $role) {
            $request->setCC($role->name, "oscar$i@example.com");
        }
        foreach ($template->getCustomFields() as $i => $field) {
            $request->setCustomFieldValue($field->name, 'My String');
        }
        
        // Turn it into an embedded request
        $client_id = $_ENV['HELLOSIGN_CLIENT_ID'];
        $embedded_request = new EmbeddedSignatureRequest($request, $client_id);
        
        // Send it to HelloSign
        $response = $this->client->createEmbeddedSignatureRequest($embedded_request);

        $this->assertInstanceOf('HelloSign\SignatureRequest', $response);
        $this->assertNotNull($response->getId());
        $signatures = $response->getSignatures();
        return $signatures[0]->getId();
    }

    /**
     * @depends testCreateEmbeddedSignatureRequest
     * @group read
     */
    public function testGetEmbeddedSignUrl($id)
    {
        $response = $this->client->getEmbeddedSignUrl($id);
        $sign_url = $response->getSignUrl();

        $this->assertNotEmpty($sign_url);
    }
}
