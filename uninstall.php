<?php
/*
 * Modulsuche fuer REDAXO 5
 * Author: Daniel Springer
 */

// Tabelle Module um zwei Felder erleichtern
rex_sql_table::get(rex::getTable('module'))
    ->removeColumn('modulsuche_module_thumbnail')
    ->removeColumn('modulsuche_module_description')
    ->alter();

// Medien-Effekt löschen
$sql = rex_sql::factory();
$sql->setTable(rex::getTablePrefix().'media_manager_type');
$sql->setWhere(['name'=>'modulsuche_thumbnail']);
$sql->delete();

$sql->setTable(rex::getTablePrefix().'media_manager_type_effect');
$sql->setWhere(['createuser'=>'modulsuche']);
$sql->delete();