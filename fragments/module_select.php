<?php

// Erweiterte Ansicht - "Von Hand" die Liste zusammenbauen, da rex_select nciht die Custom-Möglichkeiten für HTML-bietet
if(rex_addon::get('mf_modulsuche')->getConfig('display_extended') === '|1|') {
    #dump($this);

    $liveSearch = '';
    if (count($this->items) > rex_addon::get('mf_modulsuche')->getConfig('limit')) {
        $liveSearch = ' data-live-search="true"';
    }
    $select = '<select name="standard" size="1" class="form-control selectpicker extended-view" onchange="window.location = this.options[this.selectedIndex].value;"'.$liveSearch.'>';
    $select .= '<option value="" style="display:none;" class="select-placeholder" selected="selected" disabled="disabled">'.rex_i18n::msg('add_block').'</option>';

    foreach ($this->items as $item) {
        #dump($item);


        $sql =rex_sql::factory();
        $sql->setTable(rex::getTable('module'));
        $sql->setWhere(['id'=>$item['id']]);
        $sql->select();

        $fileUrl = rex_url::addonAssets('mf_modulsuche','modulthumbnail_platzhalter.jpg');
        if($sql->getValue('mf_modulsuche_module_thumbnail') !== '') {
            $fileUrl = '/media/mf_modulsuche_thumbnail/'.$sql->getValue('mf_modulsuche_module_thumbnail');
        }
        $thumbnail = '<img src=\''.$fileUrl.'\' alt=\'Thumbnail '.$sql->getValue('mf_modulsuche_module_thumbnail').'\'>';

        $description = '';
        if($sql->getValue('mf_modulsuche_module_description') !== '') {
            $description = ' <span class=\'text-muted\'><small>'.$sql->getValue('mf_modulsuche_module_description').'</small></span>';
        }

        $select .=
            '<option
                data-content="'.$thumbnail.$item['title'].$description.'"
                value="'.str_replace('&amp;', '&', $item['href']).'">
                '.$item['title'].
            '</option>';
        $select .= '<option data-divider="true"></option>';
    }
    $select .= '</select>';

}
// Normale Ansicht - Via rex_select
else {
    $select = new rex_select();
    #$select->setId('rex-add-select-pos-' . $this->position);
    $select->setSize('1');
    $select->addOption(rex_i18n::msg('add_block'), '', 0, 0,
        [
            "style" => "display:none;",
            "class" => "select-placeholder",
            "selected" => "selected",
            "disabled" => "disabled"
        ]);
    foreach ($this->items as $item) {
        $select->addOption($item['title'], str_replace('&amp;', '&', $item['href']));
    }
    #$select->setAttribute('id', 'rex-select-pos-' . $this->position);
    $select->setAttribute('class', 'form-control selectpicker');
    $select->setAttribute('onchange', 'window.location = this.options[this.selectedIndex].value;');

    if (count($this->items) > rex_addon::get('mf_modulsuche')->getConfig('limit')) {
        $select->setAttribute('data-live-search', 'true');
    }
    $select = $select->get();
}

echo $select;