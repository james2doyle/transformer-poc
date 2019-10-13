<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use App\Render;

class RenderTest extends TestCase
{
    /** @test */
    public function itCanRenderSimpleTemplates()
    {
        $output = (new Render(__DIR__ . '/stubs'))('simple.twig', [
            'hello' => 'world',
        ]);

        $this->assertContains('world', $output);
    }

    /** @test */
    public function itCannotRenderTemplatesWithDisallowedFunctions()
    {
        $this->expectException(SecurityNotAllowedFunctionError::class);
        (new Render(__DIR__ . '/stubs'))('whitelisted.twig', [
            'hello' => 'world',
        ]);
    }
}
