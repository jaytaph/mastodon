<?php

namespace App\Tests\Feature;

use App\Service\WebfingerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WebfingerTest extends KernelTestCase
{
    public function testWebfinger()
    {
        self::bootKernel();
        $service = static::getContainer()->get(WebfingerService::class);;

        $info = $service->fetch("Skoop@phpc.social");
        $this->assertArrayHasKey("subject", $info);
        $this->assertEquals("acct:jaytest@mastodon.nl", $info['subject']);


        $info = $service->fetch("jaytest@mastodon.nl");
        $this->assertArrayHasKey("subject", $info);
        $this->assertEquals("acct:jaytest@mastodon.nl", $info['subject']);

        $info = $service->fetch("jaytaph@dhpt.nl");
        $this->assertArrayHasKey("subject", $info);
        $this->assertEquals("acct:jaytaph@dhpt.nl", $info['subject']);
    }
}
