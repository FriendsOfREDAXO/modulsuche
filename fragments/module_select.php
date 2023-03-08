<?php
/*
 * Modulsuche fuer REDAXO 5
 * Author: Daniel Springer
 */

// Überprüfen ob bloecks installiert ist
$bloecks = false;
if(rex_addon::exists('bloecks')) {
    $addons = rex_addon::getInstalledAddons();

    if(isset($addons['bloecks'])) {
        $bloecks    = $addons['bloecks']->isAvailable();
        $articleId  = rex_request('article_id', 'int');
        $categoryId = rex_request('category_id', 'int');
        $clang      = rex_request('clang', 'int');
        $ctype      = rex_request('ctype', 'int');

        $context    = new rex_context([
            'page' => rex_be_controller::getCurrentPage(),
            'article_id' => $articleId,
            'clang' => $clang,
            'ctype' => $ctype,
            'category_id' => $categoryId,
            'function' => 'add',
        ]);
    }
} // Ende if bloecks

// Erweiterte Ansicht - "Von Hand" die Liste zusammenbauen, da rex_select nicht die Custom-Möglichkeiten für HTML-bietet
if(rex_addon::get('modulsuche')->getConfig('display_extended') === '|1|') {
    $liveSearch = '';
    if (count($this->items) > rex_addon::get('modulsuche')->getConfig('limit')) {
        $liveSearch = ' data-live-search="true"';
    }
    $select = '<select name="standard" size="1" class="form-control selectpicker extended-view" onchange="window.location = this.options[this.selectedIndex].value;"'.$liveSearch.'>';
    $select .= '<option value="" style="display:none;" class="select-placeholder" selected="selected" disabled="disabled">'.rex_i18n::msg('add_block').'</option>';

    // Wenn bloecks installiert ist
    if($bloecks){
        $clipBoardContents = modulsuche::getClipboardContents();
        #dump($clipBoardContents);
        if($clipBoardContents['action'] === "copy" || $clipBoardContents['action'] === "cut") {
            $sliceDetails = modulsuche::getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
            #dump($sliceDetails);
            $context->setParam('source_slice_id', $clipBoardContents['slice_id']);
            if ($sliceDetails['article_id']) {
                // Option aus cut/copy hinzufügen
                $moduleIcon = '';
                if($clipBoardContents['action'] === 'copy') {
                    $moduleIcon .= '<i class=\'fa fa-clipboard\' aria-hidden=\'true\' style=\'margin-right: 5px;\'></i>';
                }
                elseif($clipBoardContents['action'] === 'cut') {
                    $moduleIcon .= '<i class=\'fa fa-scissors\' aria-hidden=\'true\' style=\'margin-right: 5px;\'></i>';
                }
                $select .=
                    '<option
                data-content="'.$moduleIcon.rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $sliceDetails['module_id'], rex_article::get($sliceDetails['article_id'])->getName()).'"
                value="'.$context->getUrl(
                        [
                            'module_id' => $sliceDetails['module_id'],
                            'article_id' => $articleId,
                            'clang' => $clang,
                            'ctype' => $ctype,
                            'category_id' => $categoryId,
                            'function' => 'add',
                            'source_slice_id' => $context->getParam('source_slice_id'),
                            'slice_id' => '-1',
                        ]).'"></option>';
                $select .= '<option data-divider="true"></option>';
            }
        }
    } //Ende if bloecks

    foreach ($this->items as $item) {
        #dump($item);

        $sql =rex_sql::factory();
        $sql->setTable(rex::getTable('module'));
        $sql->setWhere(['id'=>$item['id']]);
        $sql->select();

        $fileUrl = rex_url::addonAssets('modulsuche','modulthumbnail_platzhalter.jpg');
        if($sql->getValue('modulsuche_module_thumbnail') !== '') {
            $fileUrl = '/media/modulsuche_thumbnail/'.$sql->getValue('modulsuche_module_thumbnail');
        }
        $thumbnail = '<img src=\''.$fileUrl.'\' alt=\'Thumbnail '.$sql->getValue('modulsuche_module_thumbnail').'\'>';

        $description = '';
        if($sql->getValue('modulsuche_module_description') !== '') {
            $description = ' <span class=\'text-muted\'><small>'.$sql->getValue('modulsuche_module_description').'</small></span>';
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
            "style"     => "display:none;",
            "class"     => "select-placeholder",
            "selected"  => "selected",
            "disabled"  => "disabled"
        ]);

    // Wenn bloecks installiert ist
    if($bloecks){
        $clipBoardContents = modulsuche::getClipboardContents();
        if(isset($clipBoardContents['action']) && ($clipBoardContents['action'] === "copy" || $clipBoardContents['action'] === "cut")) {
            $sliceDetails = modulsuche::getSliceDetails($clipBoardContents['slice_id'], $clipBoardContents['clang']);
            $context->setParam('source_slice_id', $clipBoardContents['slice_id']);
            if ($sliceDetails['article_id']) {
                $moduleIcon = '';
                if($clipBoardContents['action'] === 'copy') {
                    $moduleIcon .= '<i class=\'fa fa-clipboard\' aria-hidden=\'true\' style=\'margin-right: 5px;\'></i>';
                }
                elseif($clipBoardContents['action'] === 'cut') {
                    $moduleIcon .= '<i class=\'fa fa-scissors\' aria-hidden=\'true\' style=\'margin-right: 5px;\'></i>';
                }
                // Option aus cut/copy hinzufügen
                $select->addOption(
                    rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $sliceDetails['module_id'], rex_article::get($sliceDetails['article_id'])->getName()), // Name
                    str_replace('&amp;','&',rex_url::currentBackendPage( // Value (str_replace, weil irgendwie doppelte &amp; eingebaut wurden
                    [
                        'page'=>'content/edit',
                        'article_id' => $articleId,
                        'clang' => $clang,
                        'ctype' => $ctype,
                        'slice_id' => '-1',
                        'function' => 'add',
                        'module_id' => $sliceDetails['module_id'],
                        'category_id' => $categoryId,
                        'source_slice_id' => $context->getParam('source_slice_id'),
                    ])),
                    '',
                    0,
                    ['data-content'=> $moduleIcon.rex_addon::get('bloecks')->i18n('insert_slice', $sliceDetails['name'], $sliceDetails['module_id'], rex_article::get($sliceDetails['article_id'])->getName()) ]
                );
                // Divider-Option einfügen
                $select->addOption('', '', '',0,['data-divider'=>'true'] ); // $name, $value, $id, $parent_id (Position: was kommt vor dieser Option), $attributes[]
            }
        }
    } //Ende if bloecks

    // Module auflisten
    foreach ($this->items as $item) {
        $select->addOption($item['title'], str_replace('&amp;', '&', $item['href']));
    }
    #$select->setAttribute('id', 'rex-select-pos-' . $this->position);
    $select->setAttribute('class', 'form-control selectpicker');
    $select->setAttribute('onchange', 'window.location = this.options[this.selectedIndex].value;');

    if (count($this->items) > rex_addon::get('modulsuche')->getConfig('limit')) {
        $select->setAttribute('data-live-search', 'true');
    }
    $select = $select->get();
}

echo $select;
