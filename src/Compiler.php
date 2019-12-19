<?php
declare(strict_types=1);

namespace Mjml;

/**
 * Class Compiler
 * @package Mjml
 */
class Compiler
{
  /** @var FactoryInterface $factory */
  protected $factory;

  /**
   * Compiler constructor.
   * @param FactoryInterface $factory
   */
  public function __construct(FactoryInterface $factory)
  {
    $this->factory = $factory;
  }

  /**
   * @param string $input
   * @param string $output_filepath
   * @return bool
   * @throws CompileException
   */
  public function compile(string $input, string $output_filepath): bool
  {
    $node = $this->factory->createNodeExe();
    $mjml  = $this->factory->createCompiler();
    $error = '';

    if ($node->isExecutable() && $mjml->isExecutable()) {
      $cmd = "{$node} {$mjml} -is";
      $spec = [
        0 => ['pipe', 'r'],
        1 => ['file', $output_filepath, 'w'],
        2 => ['pipe', 'w']
      ];

      $handle = proc_open($cmd, $spec, $pipes);

      if (is_resource($handle)) {
        fwrite($pipes[0], $input, strlen($input));
        fclose($pipes[0]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        if (proc_close($handle) !== 0) {
          $error = "Non-zero exit status: {$error}";
        }
      } else {
        $error = 'Opening process did not return expected resource';
      }
    } else {
      $exe = $node->isExecutable() ? $mjml->__toString() : $node->__toString();
      $error = basename($exe) . ' not executable';
    }

    if ($error) {
      throw new CompileException($error);
    }
    return true;
  }
}
