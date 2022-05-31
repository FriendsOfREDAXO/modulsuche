<?php
/*
 * Modulsuche fuer REDAXO 5
 * Author: Daniel Springer
 */
$addon = 'modulsuche';
if (\rex::isBackend()
    && \rex_addon::get($addon)->isAvailable()
    #&& rex_addon::get($addon)->getConfig('display_extended') === '|1|'
    && rex_request::get('page', 'string') === 'content/edit'
) {
    \rex_view::addCssFile(rex_addon::get($addon)->getAssetsUrl('modulsuche-be-style.css'));
}