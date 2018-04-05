<?php

namespace Submitty\Submitty\Libraries;

use PHPUnit\Framework\TestCase;
use Submitty\Submitty\Exceptions\AuthenticationException;
use Submitty\Submitty\Exceptions\BaseException;

class ExceptionHandlerTester extends TestCase {
    public function testClassVariables() {
        /** @noinspection PhpUnhandledExceptionInspection */
        $class = new \ReflectionClass('Submitty\Submitty\Libraries\ExceptionHandler');
        $properties = $class->getProperties();
        for ($i = 0; $i < count($properties); $i++) {
            $properties[$i]->setAccessible(true);
        }
        $this->assertEquals("log_exceptions", $properties[0]->getName());
        $this->assertFalse($properties[0]->getValue());
        $this->assertEquals("display_exceptions", $properties[1]->getName());
        $this->assertFalse($properties[1]->getValue());
    }

    public function testThrowServerException() {
        $message = ExceptionHandler::handleException(new \RuntimeException("test"));
        $this->assertContains("An exception was thrown. Please contact an administrator about what you were doing that caused this exception.", $message);
        $this->assertNotContains("test", $message);
    }

    public function testThrowRuntimeException() {
        ExceptionHandler::setDisplayExceptions(true);
        $this->assertContains("test", ExceptionHandler::handleException(new \RuntimeException("test")));
        ExceptionHandler::setDisplayExceptions(false);
    }

    public function testExceptionHandlerLog() {
        $tmp_dir = FileUtils::joinPaths(sys_get_temp_dir(), Utils::generateRandomString());
        $this->assertTrue(FileUtils::createDir($tmp_dir));
        $this->assertTrue(FileUtils::createDir(FileUtils::joinPaths($tmp_dir, 'site_errors')));
        Logger::setLogPath($tmp_dir);
        date_default_timezone_set("America/New_York");
        $date = getdate(time());
        $filename = $date['year'].Utils::pad($date['mon']).Utils::pad($date['mday']).'.log';
        ExceptionHandler::setDisplayExceptions(false);
        ExceptionHandler::setLogExceptions(true);
        ExceptionHandler::handleException(new BaseException("test", array("test"=>"b", "test2"=>array('a','c'))));
        $file = FileUtils::joinPaths($tmp_dir, 'site_errors', $filename);
        $this->assertFileExists($file);
        $actual = file_get_contents($file);
        $this->assertRegExp('/[0-9]{2}\:[0-9]{2}\:[0-9]{2}\ [0-9]{2}\/[0-9]{2}\/20[0-9]{2} \- FATAL ERROR\nSubmitty.+/', $actual);
        $this->assertRegExp('/Extra Details:\n\ttest: b\n\ttest2:\n\t\ta\n\t\tc/', $actual);
        ExceptionHandler::setLogExceptions(false);
        $this->assertTrue(FileUtils::recursiveRmdir($tmp_dir));
    }

    private function authenticate($username, $password) {
        throw new AuthenticationException($username. "  ". $password);
    }


    public function testScrubPassword() {
        try {
            $this->authenticate("test", "test");
            $this->fail("Should have thrown exception");
        }
        catch(AuthenticationException $e) {
            ExceptionHandler::setDisplayExceptions(true);
            $message = ExceptionHandler::handleException($e);
            $this->assertContains("Submitty\\Submitty\\Libraries\\ExceptionHandlerTester->authenticate()", $message);
        }
    }

    public function testDisplayJustExceptionMessage() {
        ExceptionHandler::setDisplayExceptions(false);
        $exception = new BaseException("exception message");
        $exception->setDisplayMessage(true);
        $message = ExceptionHandler::handleException($exception);
        $this->assertContains("exception message", $message);
        $this->assertNotContains("Stack Trace", $message);
    }
}