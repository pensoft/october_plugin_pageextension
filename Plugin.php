<?php

namespace Pensoft\PageExtension;

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
        \Event::listen('backend.form.extendFields', function ($widget){
            if (!$widget->model instanceof Page) return;

            $widget->addFields([
                'settings[header_image]' => [
                    'label'   => 'Header image',
                    'type'    => 'text',
                    'comment' => 'pensoft.pageextension::lang.fields.header_image.comment',
                    'tab'     => 'cms::lang.editor.settings'
                ],
            ],'primary'
            );
        });
    }
}
