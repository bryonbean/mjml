<?php
declare(strict_types=1);

namespace Mjml;

/**
 * Class Factory
 * @package Mjml
 */
class Factory implements FactoryInterface
{

    /**
     * @inheritDoc
     */
    public function createCompiler(): File
    {
        $mjml_path = dirname(__DIR__) . '/node_modules/.bin/mjml';
        return new File($mjml_path);
    }
}