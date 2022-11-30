<?php

/**
 * KLasse mit Hilfsfunktionen für das AddOn Bloecks
 * Ursprünglich von Thorben Jaworr
 * https://github.com/FriendsOfREDAXO/module_preview/blob/9e5ac57862c9bd3aeda2d233227c2b2abda6087b/lib/module_preview.php
 *
 */

class modulsuche
{
    public static function hasClipboardContents(): bool {
        $cookie = self::getClipboardContents();

        if($cookie) {
            return true;
        }

        return false;
    }

    public static function getClipboardContents() {
        return @json_decode(rex_request::cookie('rex_bloecks_cutncopy', 'string', ''), true);
    }

    public static function getSliceDetails($sliceId, $clangId) {
        if($sliceId && $clangId) {
            $sql = rex_sql::factory();
            $sql->setDebug(false);
            $sql->setQuery('select ' . rex::getTablePrefix() . 'article_slice.article_id, ' . rex::getTablePrefix() . 'article_slice.module_id, ' . rex::getTablePrefix() . 'module.name from ' . rex::getTablePrefix() . 'article_slice left join ' . rex::getTablePrefix() . 'module on ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id where ' . rex::getTablePrefix() . 'article_slice.id=? and ' . rex::getTablePrefix() . 'article_slice.clang_id=?', [$sliceId, $clangId]);
            return $sql->getArray()[0];
        }
    }
}