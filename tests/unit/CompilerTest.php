<?php
declare(strict_types=1);

namespace Mjml;

use PHPUnit\Framework\TestCase;

function proc_open($cmd, array $descriptorspec, ?array &$pipes = [], $cwd = null, array $env = null, array $other_options = null)
{
  foreach ($descriptorspec as $idx => $spec) {
    $io = new \stdClass();
    if ($spec[0] === 'file') {
      list($io->type, $io->file, $io->mode) = $spec;
    } else {
      list($io->type, $io->mode) = $spec;
    }
    $pipes[$idx] = $io;
  }
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

function fwrite($handle, $string, $length = null)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

function fclose($handle)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

function is_resource($var)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

function strlen($string)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return \strlen($string);
}

function stream_get_contents($handle, $maxlength = null, $offset = null)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

function proc_close($process)
{
  CompilerTest::setReceived(__FUNCTION__, func_get_args());
  return CompilerTest::getReturn(__FUNCTION__);
}

class CompilerTest extends TestCase
{
  private static $returned = [];
  private static $received = [];

  public function tearDown(): void
  {
    parent::tearDown();
    self::$received = [];
    self::$returned = [];
  }

  public function testProcOpenInput()
  {
    $this->runCompile();

    $expected = [
      '/path/to/bin/node /path/to/local/mjml -is',
      [
        ['pipe', 'r'],
        ['file', '/some/file/location/template.ctp', 'w'],
        ['pipe', 'w'],
      ],
      [
        (object)['type' => 'pipe', 'mode' => 'r'],
        (object)['type' => 'file', 'file' => '/some/file/location/template.ctp', 'mode' => 'w'],
        (object)['type' => 'pipe', 'mode' => 'w'],
      ],
    ];
    $actual = $this->getReceived('proc_open');
    $this->assertEquals($expected, $actual);
  }

  public function testIsResourceInput()
  {
    $this->runCompile();
    $expected = [(object)['resource' => 'mjml']];
    $actual = $this->getReceived('is_resource');
    $this->assertEquals($expected, $actual);
  }

  public function testFwriteInput()
  {
    $this->runCompile();
    $input = '<mjml>Hello world!</mjml>';

    $expected = [
      (object)['type' => 'pipe', 'mode' => 'r'],
      $input,
      strlen($input)
    ];
    $actual = $this->getReceived('fwrite');
    $this->assertEquals($expected, $actual);
  }

  public function testFcloseInput()
  {
    $this->runCompile();

    $expected = [(object)['type' => 'pipe', 'mode' => 'r']];
    $actual = $this->getReceived('fclose');
    $this->assertEquals($expected, $actual);

    $expected = [(object)['type' => 'pipe', 'mode' => 'w']];
    $actual = $this->getReceived('fclose');
    $this->assertEquals($expected, $actual);
  }

  public function testStreamGetContentsInput()
  {
    $this->runCompile();
    $expected = [(object)['type' => 'pipe', 'mode' => 'w']];
    $actual = $this->getReceived('stream_get_contents');
    $this->assertEquals($expected, $actual);
  }

  public function testProcCloseInput()
  {
    $this->runCompile();
    $expected = [(object)['resource' => 'mjml']];
    $actual = $this->getReceived('proc_close');
    $this->assertEquals($expected, $actual);
  }

  public function testCompileGivenProcOpenDoesNotReturnResource()
  {
    $this->expectException(CompileException::class);
    $this->expectExceptionMessage('Opening process did not return expected resource');

    $this->runCompile(null, true, true, false);
  }

  public function testCompileGivenExecutableExitsWithNonZero()
  {
    $error = uniqid();
    $this->expectException(CompileException::class);
    $this->expectExceptionMessage("Non-zero exit status: {$error}");
    $this->runCompile($error);
  }

  public function testCompileGivenNodeExeNotExecutable()
  {
    $this->expectException(CompileException::class);
    $this->expectExceptionMessage("node not executable");
    $this->runCompile(null, false);
  }

  public function testCompileGivenMjmlExeNotExecutable()
  {
    $this->expectException(CompileException::class);
    $this->expectExceptionMessage("mjml not executable");
    $this->runCompile(null, true, false);
  }

  /**
   * @param string $fn
   * @param array $arguments
   */
  public static function setReceived(string $fn, array $arguments)
  {
    $fn = self::getFnBasename($fn);
    if (!isset(static::$received[$fn])) {
      static::$received[$fn] = [];
    }
    static::$received[$fn][] = $arguments;
  }

  /**
   * @param string $fn
   * @return array
   */
  public function getReceived(string $fn): array
  {
    if (isset(self::$received[$fn])) {
      return array_shift(self::$received[$fn]) ?: [];
    }
    return [];
  }

  /**
   * @param string $fn
   * @return mixed|null
   */
  public static function getReturn(string $fn)
  {
    $fn = self::getFnBasename($fn);
    $expected = null;
    if (isset(static::$returned[$fn][0])) {
      // fifo
      $expected = array_shift(static::$returned[$fn]);
      return $expected;
    }
  }

  /**
   * @param string $fn
   * @param $value
   */
  public function setReturn(string $fn, $value)
  {
    if (!isset(static::$returned[$fn])) {
      static::$returned[$fn] = [];
    }
    static::$returned[$fn][] = $value;
  }

  /**
   * @param string $fn
   * @return string
   */
  private static function getFnBasename(string $fn): string
  {
    return substr(strrchr($fn, '\\'), 1);
  }

  /**
   * @param string|null $error
   * @param bool $nodeExe
   * @param bool $mjmlExe
   * @param bool $isRes
   */
  private function runCompile(string $error = null,
                              bool $nodeExe = true,
                              bool $mjmlExe = true,
                              bool $isRes = true): void
  {
    $mock_node = $this->createMockExecutable('/path/to/bin/node', $nodeExe);
    $mock_mjml = $this->createMockExecutable('/path/to/local/mjml', $mjmlExe);

    $mock_factory = $this->createMock(FactoryInterface::class);
    $mock_factory->expects($this->once())
      ->method('createNodeExe')
      ->willReturn($mock_node);
    $mock_factory->expects($this->once())
      ->method('createCompiler')
      ->willReturn($mock_mjml);

    $mjml = (object)['resource' => 'mjml'];

    $this->setReturn('proc_open', $mjml);
    $this->setReturn('is_resource', $isRes);

    if ($error) {
      $this->setReturn('stream_get_contents', $error);
      $this->setReturn('proc_close', 1);
    } else {
      $this->setReturn('proc_close', 0);
    }

    $input = '<mjml>Hello world!</mjml>';
    $sut = new Compiler($mock_factory);
    $actual = $sut->compile($input, '/some/file/location/template.ctp');
    $this->assertTrue($actual);
  }

  /**
   * @param string $path
   * @param bool $isExe
   * @return File
   */
  private function createMockExecutable(string $path, bool $isExe): File
  {
    $mock_exe = $this->createMock(File::class);
    $mock_exe->expects($this->any())
      ->method('isExecutable')
      ->willReturn($isExe);
    $mock_exe->expects($this->any())
      ->method('__toString')
      ->willReturn($path);
    return $mock_exe;
  }

}
