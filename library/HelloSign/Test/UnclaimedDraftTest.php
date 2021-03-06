<?php

namespace HelloSign\Test;

use HelloSign\SignatureRequest;
use HelloSign\UnclaimedDraft;

class UnclaimedDraftTest extends AbstractTest
{
    public function testCreateUnclaimedDraft()
    {
        $account = $this->client->getAccount();

        $request = new SignatureRequest;
        $request->enableTestMode();
        $request->setRequesterEmail($account->getEmail());
        $request->setTitle('NDA with Acme Co.');
        $request->setSubject('The NDA we talked about');
        $request->setMessage('Please sign this NDA and let\'s discuss.');
        $request->addSigner('bale@example.com', 'Bale');
        $request->addSigner('beck@example.com', 'Beck');
        $request->addCC('ancelotti@example.com');
        $request->addFile(__DIR__ . '/nda.docx');

        $client_id = $_ENV['HELLOSIGN_CLIENT_ID'];
        $draft = new UnclaimedDraft($request, $client_id);

        $response = $this->client->createUnclaimedDraft($draft);
        $sign_url = $response->getClaimUrl();


        $this->assertInstanceOf('HelloSign\UnclaimedDraft', $response);
        $this->assertNotNull($response);
        $this->assertEquals($draft, $response);

        $this->assertNotEmpty($sign_url);
    }
}
