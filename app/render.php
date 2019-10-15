<?php

namespace App;

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
    protected $tags = [
        'apply',
        'autoescape',
        'block',
        // 'deprecated',
        // 'do',
        // 'embed',
        // 'extends',
        // 'flush',
        'for',
        // 'from',
        'if',
        // 'import',
        // 'include',
        // 'macro',
        'sandbox',
        'set',
        // 'use',
        'verbatim',
        'with',
    ];

    /**
     * White list of filters to allow
     *
     * @var array
     */
    protected $filters = [
        'abs',
        'batch',
        'capitalize',
        'column',
        'convert_encoding',
        'country_name',
        'country_timezones',
        'currency_name',
        'currency_symbol',
        // 'data_uri',
        'date',
        'date_modify',
        'default',
        'escape',
        'filter',
        'first',
        'format',
        'format_currency',
        'format_date',
        'format_datetime',
        'format_number',
        'format_time',
        // 'inky',
        // 'inline_css',
        'join',
        'json_encode',
        'keys',
        'language_name',
        'last',
        'length',
        'locale_name',
        'lower',
        'map',
        // 'markdown',
        'merge',
        'nl2br',
        'number_format',
        'raw',
        'reduce',
        'replace',
        'reverse',
        'round',
        'slice',
        'sort',
        'spaceless',
        'split',
        'striptags',
        'timezone_name',
        'title',
        'trim',
        'upper',
        'url_encode',
    ];

    /**
     * White list of methods to allow
     *
     * @var array
     */
    protected $allowedMethods = [];

    /**
     * White list of properties to allow
     *
     * @var array
     */
    protected $allowedProperties = [];

    /**
     * White list of functions to allow
     *
     * @var array
     */
    protected $allowedFunctions = [
        'attribute',
        'block',
        // 'constant',
        'cycle',
        'date',
        // 'dump', // on during testing?
        // 'html_classes',
        // 'include',
        'max',
        'min',
        // 'parent',
        'random',
        'range',
        // 'source',
        // 'template_from_string',
    ];

    /**
     * @var bool
     */
    const IS_SANDBOXED = true;

    /**
     * Create the twig loader
     *
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        $env = getenv('APP_ENV') ? 'local' : getenv('APP_ENV');

        $path = $path ?? __DIR__ . '/../templates';
        $loader = new FilesystemLoader($path);
        $this->twig = new Environment($loader, [
            'cache' => $env === 'local' ? false : __DIR__ . '/../tmp', // if you want caching
            'debug' => $env === 'local',
            'auto_reload' => true,
        ]);

        if ($env === 'local' || $env === 'testing') {
            $this->twig->addExtension(new DebugExtension());
        }

        $policy = new SecurityPolicy(
            $this->tags,
            $this->filters,
            $this->allowedMethods,
            $this->allowedProperties,
            $this->allowedFunctions
        );
        $sandbox = new SandboxExtension($policy, self::IS_SANDBOXED);
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
