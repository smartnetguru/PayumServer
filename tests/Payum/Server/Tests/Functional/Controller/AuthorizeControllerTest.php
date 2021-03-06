<?php
namespace Payum\Server\Tests\Functional\Controller;

use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;
use Silex\Application;

class AuthorizeControllerTest extends ClientTestCase
{
    use ResponseHelper;

    public function testShouldAllowChooseGateway()
    {
        /** @var StorageInterface $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('FooGateway');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->update($gatewayConfig);

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('BarGateway');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->update($gatewayConfig);

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $store = $payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $store->create();
        $payment->setGatewayName(null);
        $payment->setId(uniqid());

        $store->update($payment);

        $token = $payum->getTokenFactory()->createAuthorizeToken('itDoesNotMatter', $payment, 'http://localhost');

        $crawler = $this->getClient()->request('GET', $token->getTargetUrl());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertGreaterThan(0, count($crawler->filter('.payum-choose-gateway')));
        $this->assertContains('FooGateway', $crawler->text());
        $this->assertContains('BarGateway', $crawler->text());

        $form = $crawler->filter('form')->form();
        $form['gatewayName'] = 'BarGateway';

        $crawler = $this->getClient()->submit($form);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirectionStartsWith('http://localhost/?payum_token=');
    }
}