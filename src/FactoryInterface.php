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

  /**
   * Returns a \Mjml\File instance that represents the node executable
   *
   * @return File
   */
  public function createNodeExe(): File;
}
