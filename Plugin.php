<?php

namespace Pensoft\PageExtension;

use BennoThommo\Meta\Components\MetaList;
use BennoThommo\Meta\Meta;
use Cms\Classes\Page;
use System\Classes\PluginBase;

class Plugin extends PluginBase {

    public function pluginDetails()
    {
        return [
            'name'        => 'pensoft.pageextension::lang.plugin.name',
            'description' => 'pensoft.pageextension::lang.plugin.description',
            'author'      => 'Pensoft',
            'icon'        => 'icon-leaf'
        ];
    }

    public function register()
    {
        \Event::listen('cms.template.extendTemplateSettingsFields', function ($extension, $dataHolder) {
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
