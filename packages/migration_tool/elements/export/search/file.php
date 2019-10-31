<?php
defined('C5_EXECUTE') or die(_('Access Denied.'));
$form = Loader::helper('form');
$datetime = Loader::helper('form/date_time')->translate('datetime', $_GET);
$list = (new FileSetList)->get();
$filesetOptions = array('' => t('** Choose a file set'));
foreach ($list as $fs) {
    $filesetOptions[$fs->getFileSetID()] = $fs->getFileSetName();
}
?>
<div class="form-group">
    <label class="control-label"><?=t('Keywords')?></label>
    <?=$form->text('keywords')?>
    <br/><br/>
</div>

<div class="form-group">
    <label class="control-label"><?=t('File Set')?></label>
    <?=$form->select('fsID', $filesetOptions)?>
    <br/><br/>
</div>

<div class="form-group">
    <label class="control-label"><?=t('Added on or After')?></label>
    <?=Loader::helper('form/date_time')->datetime('datetime', $datetime, true)?>
    <br/><br/>
</div>