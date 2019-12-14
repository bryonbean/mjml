# MJML Compilation for PHP

## Description

A PHP API for MailJet's mjml transpiler.

## Installation

```bash
$ composer require bryonbean/mjml
```

## Usage
```php
use Mjml\Factory;
use Mjml\Compiler;

$mjml = '
    <mjml>
        <mj-body>
            <mj-section>
                <mj-raw><p>Hello world</p></mj-raw>
            </mj-section>
        </mj-body>
    </mjml>';

$factory = new Factory();
$compiler = new Compiler($factory);
$compiler->compile($mjml, '/path/to/my/template');
```

## Contributing

Pull requests are welcome. For major changes please open an issue first to discuss what you would like to change.

## License

[MIT](https://choosealicense.com/licenses/mit/)
