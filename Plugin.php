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

    /**
     * Registers Twig markup filters available in all front-end templates.
     *
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'ensure_img_alt' => [self::class, 'ensureImgAlt'],
            ],
        ];
    }

    /**
     * Accessibility backstop for editor-authored rich text.
     *
     * Ensures every <img> carries a non-empty alt attribute so an image is
     * never reported as having "no accessible name" (WCAG 1.1.1). Behaviour:
     *   - a tag that already has a non-empty alt is left untouched;
     *   - a tag with a missing OR empty alt gets an alt derived from its
     *     filename (e.g. "one-system.png" -> "One system");
     *   - if no filename can be derived (e.g. a data: URI), an empty alt is
     *     added/kept so the image is at least marked decorative.
     *
     * This is a heuristic safety net — a filename rarely reads as well as a
     * human-authored description, so informative images should still be given
     * proper alt text in the CMS editor.
     *
     * Intended usage: {{ someRichTextField|ensure_img_alt|raw }}
     *
     * @param  string|null $html
     * @return string|null
     */
    public static function ensureImgAlt($html)
    {
        if (empty($html)) {
            return $html;
        }

        return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
            $tag = $matches[0];

            // Existing alt? (whitespace-anchored so data-alt="" is not matched)
            if (preg_match('/\salt\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $tag, $m)) {
                $value = isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1];

                // Non-empty alt already — respect the author's choice.
                if (trim($value) !== '') {
                    return $tag;
                }

                // Empty alt — fill it from the filename when we can.
                $alt = self::altFromSrc($tag);
                if ($alt === '') {
                    return $tag;
                }

                return preg_replace(
                    '/(\salt\s*=\s*)(?:"[^"]*"|\'[^\']*\')/i',
                    '$1"' . self::escapeAttr($alt) . '"',
                    $tag,
                    1
                );
            }

            // No alt attribute at all — add one (derived, or empty as fallback).
            $alt = self::altFromSrc($tag);

            return preg_replace('/<img\b/i', '<img alt="' . self::escapeAttr($alt) . '"', $tag, 1);
        }, $html);
    }

    /**
     * Derives a human-readable alt string from an <img> tag's src filename.
     * Returns '' when nothing usable can be extracted (e.g. data: URIs).
     *
     * @param  string $tag
     * @return string
     */
    protected static function altFromSrc($tag)
    {
        if (!preg_match('/\ssrc\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $tag, $m)) {
            return '';
        }

        $src = isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1];

        // Ignore inline data URIs — no meaningful filename there.
        if (stripos($src, 'data:') === 0) {
            return '';
        }

        // Strip any query string / fragment, keep the last path segment.
        $path = parse_url($src, PHP_URL_PATH);
        if (empty($path)) {
            $path = $src;
        }

        $name = rawurldecode(basename($path));       // "PollinERA approach - 2.png"
        $name = preg_replace('/\.[a-z0-9]+$/i', '', $name); // drop extension
        $name = str_replace(['-', '_'], ' ', $name);        // separators -> spaces
        $name = trim(preg_replace('/\s+/', ' ', $name));    // collapse whitespace

        if ($name === '') {
            return '';
        }

        // Capitalise the first character for a slightly tidier reading.
        return function_exists('mb_strtoupper')
            ? mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1)
            : ucfirst($name);
    }

    /**
     * Escapes a string for safe use inside a double-quoted HTML attribute.
     *
     * @param  string $value
     * @return string
     */
    protected static function escapeAttr($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}