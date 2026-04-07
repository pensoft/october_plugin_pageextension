<?php

namespace Pensoft\PageExtension;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'pensoft.pageextension::lang.plugin.name',
            'description' => 'pensoft.pageextension::lang.plugin.description',
            'author'      => 'Pensoft',
            'icon'        => 'icon-leaf'
        ];
    }

    public function register(): void
    {
        \Event::listen('cms.template.extendTemplateSettingsFields', function ($extension, $dataHolder): void {
            if ($dataHolder->templateType === 'page') {
                $dataHolder->settings[] = [
                    'property' => 'header_image',
                    'title' => 'Header Image',
                    'type' => 'mediafinder'
                ];

                $dataHolder->settings[] = [
                    'property' => 'seo_keywords',
                    'title' => 'Meta Keywords',
                    'type' => 'text',
                    'tab' => 'cms::lang.editor.meta',
                ];

                $dataHolder->settings[] = [
                    'property' => 'header_text',
                    'title' => 'Header text',
                    'type' => 'text',
                ];
            }
        });
    }
}