<?php

namespace SwooleTW\Http\Tests\Scripts;

use PHPUnit\Framework\TestCase;
use SwooleTW\Http\Scrips\Template;

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
     *
     */
    public function testTemplateGeneratesSameOutput(): void
    {
        $template = Template::prepare($this->template, ['name' => 'test']);

        $this->assertSame($template, $this->output);
    }

}
