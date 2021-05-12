<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

use LSVH\WordPress\Plugin\UserClassification\Plugin;
use \Twig\Environment;
use \Twig\Extra\String\StringExtension;
use \Twig\Loader\FilesystemLoader;
use \Twig\TwigFilter;

abstract class BaseComponent implements Component
{
    protected $plugin;
    protected $templateEngine;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;

        $loader = new FilesystemLoader(__DIR__ . '/Templates/');
        $this->templateEngine = new Environment($loader, []);
        $this->templateEngine->addExtension(new StringExtension());
        $this->templateEngine->addFilter(new TwigFilter('__', function ($value) {
            return __($value, $this->plugin->getDomain());
        }));
    }

    protected function renderTemplate(string $name, array $variables = []): string
    {
        return $this->templateEngine->render($name, $variables);
    }

    protected function renderTemplateBlock(string $templateName, string $blockName, array $variables = []): string
    {
        $template = $this->templateEngine->load($templateName);
        return $template->renderBlock($blockName, $variables);
    }
}
