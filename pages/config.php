<?php
if(rex_request('func', 'string') !== "edit") {
    $addon = rex_addon::get('mf_modulsuche');
    $form = rex_config_form::factory($this->getProperty('package'));

    $form->addFieldset(rex_i18n::msg('mf_modulsuche_search'));

    $field = $form->addTextField('limit');
    $field->setLabel(rex_i18n::msg('mf_modulsuche_limit'));
    $field->setAttribute('type', 'number');
    $field->setAttribute('style', 'width: 70px');

    $form->addFieldset(rex_i18n::msg('mf_modulsuche_display_type'));

    $field = $form->addCheckboxField('display_extended');
    $field->setLabel(rex_i18n::msg('mf_modulsuche_display_type_extended'));
    $field->addOption(rex_i18n::msg('mf_modulsuche_display_type_extended_label'), 1);

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', rex_i18n::msg('mf_modulsuche_settings'), false);
    $fragment->setVar('body', $form->get(), false);
    echo $fragment->parse('core/page/section.php');

    if ($addon->getConfig('display_extended') == "|1|") {

        $list = rex_list::factory('SELECT id,name,mf_modulsuche_module_thumbnail,mf_modulsuche_module_description FROM ' . rex::getTable('module'));
        #dump($list);
        // Optionen der Liste
        $list->setCaption(rex_i18n::msg('mf_modulsuche_available_modules'));
        $list->addTableAttribute('class', 'table-hover');
        // Columns
        $list->removeColumn('mf_modulsuche_module_thumbnail');
        $list->setColumnLabel('id', rex_i18n::msg('mf_modulsuche_id'));
        $list->setColumnLabel('name', rex_i18n::msg('mf_modulsuche_name'));
        // Preview-Column setzen
        $list->addColumn(rex_i18n::msg('mf_modulsuche_module_thumbnail'), '', 1, ['<th>###VALUE###</th>', '<td><img src="/media/mf_modulsuche_thumbnail/###mf_modulsuche_module_thumbnail###" width="100" height="100" alt="Thumbnail ###name###"></td>']);
        // Description
        $list->setColumnLabel('mf_modulsuche_module_description', rex_i18n::msg('mf_modulsuche_module_description'));
        // Funktionen der Liste
        $list->setColumnLabel('edit', '');
        $list->addColumn('edit', rex_i18n::msg('mf_modulsuche_edit'));
        $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###', 'start' => rex_request('start', 'int', 0)]);
        $list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###', 'start' => rex_request('start', 'int', 0)]);
        // Holzhammer: Leere Bilder suchen und durch Platzhalter ersetzen
        $list = $list->get();
        $list = str_replace('src="/media/mf_modulsuche_thumbnail/"','src="'.rex_url::addonAssets('mf_modulsuche','modulthumbnail_platzhalter.jpg').'"', $list);
        // Ins Fragment packen
        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', rex_i18n::msg('mf_modulsuche_module'), false);
        $fragment->setVar('body', $list, false);
        echo $fragment->parse('core/page/section.php');
    }
} // Eo if not edit

// If edit
if(rex_request('func','string') === "edit" && rex_request('id','int') !== "") {
    $id = rex_request('id', 'int');

    $formLabel = rex_i18n::msg('mf_modulsuche_module_edit').' [ID: '.rex_get('id').']';

    $form = rex_form::factory(rex::getTable('module'), '', 'id='.$id);

    $field = $form->addTextField('name');
    $field->setLabel(rex_i18n::msg('mf_modulsuche_name'));

    $field = $form->addTextField('mf_modulsuche_module_description');
    $field->setLabel(rex_i18n::msg('mf_modulsuche_module_description'));

    $field = $form->addMediaField('mf_modulsuche_module_thumbnail');
    $field->setLabel(rex_i18n::msg('mf_modulsuche_module_thumbnail'));
    $field->setTypes('jpg,jpeg,png');

    $form->addParam('id', $id);

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $formLabel, false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');

    // Bisschen hacky den Löschen-Button ausblenden
    echo '<style>#rex-page-mf-modulsuche-config #rex-addon-editmode .btn-delete{display: none !important;}</style>';
}