<?php

namespace SwooleTW\Http\Tests\Scripts;

use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Scrips\Template;

/**
 * Class TemplateTest
 * @package SwooleTW\Http\Tests\Scripts
 * @covers \SwooleTW\Http\Scrips\Template
 */
class TemplateTest extends TestCase
{

    private $template = <<<'CODE'
<?php

namespace {name};
CODE;

    private $output = <<<'CODE'
<?php

namespace test;
CODE;

    /**
     * @covers \SwooleTW\Http\Scrips\Template::prepare
     */
    public function testTemplateGeneratesSameOutput(): void
    {
        $template = Template::prepare($this->template, ['name' => 'test']);

        $this->assertSame($template, $this->output);
    }

}
