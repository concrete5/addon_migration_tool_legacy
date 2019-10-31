<?php

defined('C5_EXECUTE') or die(_('Access Denied.'));

class MigrationToolPackage extends Package
{
    protected $pkgHandle = 'migration_tool';
    protected $appVersionRequired = '5.6.4.0';
    protected $pkgVersion = '0.9.8';

    public function getPackageDescription()
    {
        return t('Generates content from a legacy 5.6 concrete5 site for import into a modern concrete5 installation.');
    }

    public function getPackageName()
    {
        return t('Migration Tool');
    }

    public function on_start()
    {
        $classes = array(
            'MigrationBatchExporter' => array('library', 'Export/BatchExporter', 'migration_tool'),
            'MigrationBatch' => array('model', 'MigrationBatch', 'migration_tool'),
            'MigrationBatchObjectCollection' => array('model', 'MigrationBatchObjectCollection', 'migration_tool'),
            'MigrationBatchItem' => array('model', 'MigrationBatchItem', 'migration_tool'),
            'ExportManager' => array('library', 'Export/ExportManager', 'migration_tool'),
            'AbstractExportType' => array('library', 'Export/Type/AbstractExportType', 'migration_tool'),
            'SinglePageExportType' => array('library', 'Export/Type/SinglePageExportType', 'migration_tool'),
            'UserExportType' => array('library', 'Export/Type/UserExportType', 'migration_tool'),
            'PageExportType' => array('library', 'Export/Type/PageExportType', 'migration_tool'),
            'StackExportType' => array('library', 'Export/Type/StackExportType', 'migration_tool'),
            'FileExportType' => array('library', 'Export/Type/FileExportType', 'migration_tool'),
            'FileSetExportType' => array('library', 'Export/Type/FileSetExportType', 'migration_tool'),
            'ExportTypeInterface' => array('library', 'Export/Type/ExportTypeInterface', 'migration_tool'),
            'ExportSearchResultFormatterInterface' => array('library', 'Export/SearchResult/ExportSearchResultFormatterInterface', 'migration_tool'),
            'StandardExportSearchResultFormatter' => array('library', 'Export/SearchResult/StandardExportSearchResultFormatter', 'migration_tool'),
            'StandardExportSearchResultFormatterInterface' => array('library', 'Export/SearchResult/StandardExportSearchResultFormatterInterface', 'migration_tool'),
        );
        Loader::registerAutoload($classes);
    }
    public function install()
    {
        $pkg = parent::install();
        Loader::model('single_page');
        SinglePage::add('/dashboard/migration', $pkg);
        SinglePage::add('/dashboard/migration/export', $pkg);
    }
}
