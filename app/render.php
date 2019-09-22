<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityPolicy;

/**
 * Return an instance of a twig renderer
 */
class Render
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * White list of tags to allow
     *
     * @var array
     */
    protected $tags = ['if', 'for'];

    /**
     * White list of filters to allow
     *
     * @var array
     */
    protected $filters = ['upper', 'lower', 'join', 'escape', 'striptags', 'title', 'slice'];

    /**
     * Create the twig loader
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $this->twig = new Environment($loader, [
            'cache' => false, // __DIR__ . '/../tmp', // if you want caching
            'debug' => true,
            'auto_reload' => true,
        ]);
        $this->twig->addExtension(new DebugExtension());

        $policy = new SecurityPolicy($this->tags, $this->filters);
        $sandbox = new SandboxExtension($policy, true);
        $this->twig->addExtension($sandbox);
    }

    /**
     * Return the rendered template
     *
     * @param string $template
     * @param array $data
     *
     * @return string
     */
    public function __invoke(string $template, array $data)
    {
        return $this->twig->render($template, ['data' => $data]);
    }
}
