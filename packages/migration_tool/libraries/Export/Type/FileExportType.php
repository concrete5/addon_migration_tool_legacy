<?php

class FileExportType extends AbstractExportType
{
	public function getHandle()
    {
        return 'file';
    }

    public function getPluralDisplayName()
    {
        return t('Files');
    }

    public function getHeaders()
    {
        return array(
            t('ID'),
            t('Path'),
            t('Name'),
        );
    }

    public function getResultColumns(MigrationBatchItem $exportItem)
    {
        $file = \File::getByID($exportItem->getItemIdentifier());
        return array(
            $file->getFileID(),
            $file->getRelativePath(),
            $file->getFileName(),
        );
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $file = \File::getByID($id);
            if (is_object($file)) {
                $item = new MigrationBatchItem();
                $item->setType($this->getHandle());
                $item->setItemId($id);
                $items[] = $item;
            }
        }

        return $items;
    }

    public function getResults($query)
    {
        $db = Loader::db();
        $fileList = new FileList;

        $keywords = $query['keywords'];
        $fsID = $query['fsID'];
        $datetime = Loader::helper('form/date_time')->translate('datetime', $query);
        $fileList->ignorePermissions();
        //$fileList->debug(true);
        if ($fsID) {
            $pl->filterBySet(FileSet::getByID($fsID));
        }
        if ($datetime) {
            $fileList->filterByDateAdded($datetime, '>=');
        }
        if ($keywords) {
            $fileList->filterByKeywords($keywords);
        }
        $fileList->setItemsPerPage(0);
        $results = $fileList->getPage();

        $items = array();
        foreach ($results as $row) {
            $item = new MigrationBatchItem();
            $item->setItemId($row->getFileID());
            $items[] = $item;
        }

        return $items;
    }

    public function exportCollection($collection, \SimpleXMLElement $element)
    {
        $db = Loader::db();
        $node = $element->addChild('files');
        foreach ($collection->getItems() as $item) {
            $file = \File::getByID($item->getItemIdentifier());
            if (is_object($file)) {
                
                //$attribs = $this->getSetCollectionAttributes();
                $itemNode = $node->addChild('file');
                $itemNode->addAttribute('fID', ContentExporter::replaceFileWithPlaceHolder($file->getFileID()));                
                $itemNode->addAttribute('title', $file->getTitle());
                $itemNode->addAttribute('name', $file->getFilename());
                $itemNode->addAttribute('prefix', $file->getPrefix());
                //$p->addAttribute('path', $this->getRelativePath());
                //$p->addAttribute('filename', $this->getCollectionFilename());
                $itemNode->addAttribute('date-added', $file->getDateAdded());
                $itemNode->addAttribute('description', $file->getDescription());
                if ($uID = $file->getAuthorUserID()) {
                    $author = UserInfo::getByID($uID);
                    if ($author) {
                        $authorName = $author->getUserName();
                    }
                }
                if ($authorName) {
                    $itemNode->addAttribute('author', $authorName);
                }

                $akIDs = $db->GetCol("select akID from FileAttributeValues where fID = ? and fvID = ?", array($file->getFileID(), $file->getFileVersionID()));
                if (count($akIDs) > 0) {
                    $attributes = $itemNode->addChild('attributes');
                    foreach($akIDs as $akID) {
                        $ak = FileAttributeKey::getByID($akID);
                        $av = $file->getAttributeValueObject($ak);
                        $cnt = $ak->getController();
                        $cnt->setAttributeValue($av);
                        $akx = $attributes->addChild('attributekey');
                        $akx->addAttribute('handle', $ak->getAttributeKeyHandle());
                        $cnt->exportValue($akx);
                    }
                }

            }
        }
    }
}