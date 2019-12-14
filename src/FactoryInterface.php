<?php
declare(strict_types=1);

namespace Mjml;

/**
 * Interface FactoryInterface
 * @package Mjml
 */
interface FactoryInterface
{
  /**
   * Returns a \Mjml\File object that represents the mjml executable
   *
   * @return \Mjml\File
   */
  public function createCompiler(): File;
}
