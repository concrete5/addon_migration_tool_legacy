<?php
class DashboardMigrationExportController extends DashboardBaseController
{

    public function delete_batch()
    {
        $id = $_POST['id'];
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (!is_object($batch)) {
            $this->error->add(t('Invalid Batch'));
        }
        if (!$this->error->has()) {
            if (!$this->token->validate('delete_batch')) {
                $this->error->add($this->token->getErrorMessage());
            }
        }
        if (!$this->error->has()) {
            $batch->delete();
            $this->redirect('/dashboard/migration/batches', 'batch_deleted');
            exit;
        }
        $this->view();
    }

    public function remove_from_batch()
    {
        $id = $_POST['id'];
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (!is_object($batch)) {
            $this->error->add(t('Invalid Batch'));
        }
        if (!$this->token->validate("remove_from_batch")) {
            $this->error->add($this->token->getErrorMessage());
        }
        $r = new stdClass;
        if (!$this->error->has()) {

            $r->error = false;
            $r->pages = array();
            foreach((array) $_POST['batchPageID'] as $cID) {
                $r->pages[] = $cID;
                $batch->removePageID($cID);
            }

        } else {
            $r->error = true;
            $r->messages = $this->error->getList();
        }
        print Loader::helper('json')->encode($r);
        exit;
    }

    public function add_to_batch($id = null)
    {
        $batch = MigrationBatch::getByID($id);
        if (is_object($batch)) {

            $exporters = new ExportManager();
            if (!empty($_REQUEST['item_type'])) {
                $selectedItemType = $exporters->driver($_REQUEST['item_type']);
                if (is_object($selectedItemType)) {
                    $this->set('selectedItemType', $selectedItemType);
                }
            }
            $drivers = $exporters->getDrivers();
            usort($drivers, function ($a, $b) {
                return strcasecmp($a->getPluralDisplayName(), $b->getPluralDisplayName());
            });
            $this->set('drivers', $drivers);
            $this->set('batch', $batch);
            $this->set('request', $this->request);
            $this->set('pageTitle', t('Add To Batch'));
        } else {
            $this->view();
        }
    }

    public function view_batch($id = null)
    {
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (is_object($batch)) {
            $this->set('batch', $batch);
        }
    }

    public function batch_deleted()
    {
        $this->set('message', t('Batch deleted.'));
        $this->view();
    }

    public function add_items_to_batch()
    {
        if (!$this->token->validate('add_items_to_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $exporters = new ExportManager();
        $batch = MigrationBatch::getByID($_REQUEST['batch_id']);

        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }

        $selectedItemType = false;
        if (!empty($_REQUEST['item_type']) && $_REQUEST['item_type']) {
            $selectedItemType = $exporters->driver($_REQUEST['item_type']);
        }

        if (!is_object($selectedItemType)) {
            $this->error->add(t('Invalid item type.'));
        }

        if (!$this->error->has()) {
            $values = $_REQUEST['id'];
            $exportItems = $selectedItemType->getItemsFromRequest($values[$selectedItemType->getHandle()]);
            $collection = $batch->getObjectCollection($selectedItemType->getHandle());
            if (!is_object($collection)) {
                $collection = new MigrationBatchObjectCollection();
                $collection->setType($selectedItemType->getHandle());
            }
            foreach ($exportItems as $item) {
                if (!$collection->contains($item)) {
                    $item->setCollection($collection);
                    $collection->getItems()->add($item);
                }
            }

            $this->entityManager->persist($batch);
            $this->entityManager->flush();
            $response = new JsonResponse($exportItems);

            return $response;
        }

        $r = new EditResponse();
        $r->setError($this->error);
        $r->outputJSON();
    }

    public function view()
    {
        $batches = MigrationBatch::getList();
        $this->set('batches', $batches);
    }

    public function submit() {
        if ($this->token->validate("submit")) {
            $batch = MigrationBatch::create($_POST['notes']);
            $this->redirect('/dashboard/migration/export', 'view_batch', $batch->getID());
        } else {
            $this->error->add($this->token->getErrorMessage());
        }
    }
}