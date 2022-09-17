<?php

namespace Payjp;

class PayjpTest extends TestCase
{
    public function testLogger()
    {
        $msg1 = 'test1';
        $msg2 = 'test2';
        $mock = $this->mock = $this->createMock('\Payjp\Logger\LoggerInterface');
        $mock->expects($this->once())
          ->method('info')
          ->with($msg1);
        $mock->expects($this->once())
          ->method('error')
          ->with($msg2);
        Payjp::setLogger($mock);
        Payjp::getLogger()->info($msg1);
        Payjp::getLogger()->error($msg2);
    }
}
