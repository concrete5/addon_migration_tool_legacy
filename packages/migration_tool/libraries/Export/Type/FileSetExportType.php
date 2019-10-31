<?php

Loader::model('file_set');
//SinglePageExportType
class FileSetExportType extends AbstractExportType
{
	public function getHandle()
    {
        return 'file_set';
    }

    public function getPluralDisplayName()
    {
        return t('File Sets');
    }

    public function getHeaders()
    {
        return array(
            t('Name'),
        );
    }

    public function getResultColumns(MigrationBatchItem $exportItem)
    {
        $fs = \FileSet::getByID($exportItem->getItemIdentifier());
        return array(
            $fs->getFileSetName(),
        );
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $fs = \FileSet::getByID($id);
            if (is_object($fs)) {
                $item = new MigrationBatchItem();
                $item->setType($this->getHandle());
                $item->setItemId($id);
                $items[] = $item;
            }
        }

        return $items;
    }

    public function getResults($request)
    {
        $db = Loader::db();
        $r = $db->Execute('select fsID from FileSets order by fsName');
        $items = array();
        while ($row = $r->FetchRow()) {
            $item = new MigrationBatchItem();
            $item->setItemId($row['fsID']);
            $items[] = $item;
        }

        return $items;
    }

    public function exportCollection($collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('filesets');
        foreach ($collection->getItems() as $item) {
            $fs = \FileSet::getByID($item->getItemIdentifier());
            if (is_object($fs)) {
                $itemNode = $node->addChild('fileset');
                $itemNode->addAttribute('name', $fs->getFileSetName());
                if($fs->getFileSetUserID() && $user = User::getByUserID($fs->getFileSetUserID())) {
	                $itemNode->addAttribute('user', $user->getUserName());
	            }
            	$itemNode->addAttribute('type', [
            		\FileSet::TYPE_PRIVATE => 'private',
            		\FileSet::TYPE_PUBLIC => 'public',
            		\FileSet::TYPE_STARRED => 'starred',
            		\FileSet::TYPE_SAVED_SEARCH => 'saved_search',

            	][$fs->getFileSetType()]);

                $fileSetFilesNode = $itemNode->addChild('files');
                foreach($fs->getFiles() as $displayOrder => $file){
                	$fileNode = $fileSetFilesNode->addChild('file');
                	$fileNode->addAttribute('id', ContentExporter::replaceFileWithPlaceHolder($file->getFileID()));
                	$fileNode->addAttribute('order', $displayOrder);
                }
            }
        }
    }
}