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
    $exe  = $this->factory->createCompiler();
    $node = $this->factory->createNodeExe();

    $error = '';

    if ($node->isExecutable() && $exe->isExecutable()) {
      $cmd = "{$node} {$exe} -is";
      $spec = [
        0 => ['pipe', 'r'],
        1 => ['file', $output_filepath, 'w'],
        2 => ['pipe', 'w']
      ];

      $mjml = proc_open($cmd, $spec, $pipes);

      if (is_resource($mjml)) {
        fwrite($pipes[0], $input, strlen($input));
        fclose($pipes[0]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        if (proc_close($mjml) !== 0) {
          $error = "Non-zero exit status: {$error}";
        }
      } else {
        $error = 'Opening process did not return expected resource';
      }
    } else {
      $error = "{$exe} not executable";
    }

    if ($error) {
      throw new CompileException($error);
    }
    return true;
  }
}
