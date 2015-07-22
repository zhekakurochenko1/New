<?php
namespace Cars\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Cars\Model\Cars;
use Cars\Form\CarsForm;

class CarsController extends AbstractActionController
{
    protected $carsTable;

    public function indexAction()
    {
        return new ViewModel(array(
        //    'role' => $this->zfcUserAuthentication()->getIdentity()->getRole(),
            'carss' => $this->getCarsTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity() && $this->zfcUserAuthentication()->getIdentity()->getRole() == "admin") {
            $form = new CarsForm();
            $form->get('submit')->setValue('Add');
            $request = $this->getRequest();
                if ($request->isPost()) {
                $cars = new Cars();
                $form->setInputFilter($cars->getInputFilter());
                $form->setData($request->getPost());
               
                if ($form->isValid()) {
                    $cars->exchangeArray($form->getData());                 
                    $this->getCarsTable()->saveCars($cars);
                    $adapter = new \Zend\File\Transfer\Adapter\Http();
                    $dir = getcwd(). '/public/img';
                    $data = $form->getData();
                    $filename = $data['title'];
                    $fileTlsName = $dir.$filename;// rabotaet
                    $adapter->addFilter('Rename', $fileTlsName);// rename file
                    if ($adapter->receive($fileTlsName))
                       {                                     // upload file
                        return true; }
                        // var_dump($adapter->receive($fileTlsName)); die;

                   


                    // Redirect to list of cars
                    return $this->redirect()->toRoute('cars');
                }
            }
            return array('form' => $form);
        } else {
              echo "ERROR!!!! You not admin";
            
        }
    }

    public function editAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity() && $this->zfcUserAuthentication()->getIdentity()->getRole() == "admin") {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cars', array(
                'action' => 'add'
            ));
        }
        $cars = $this->getCarsTable()->getCars($id);

        $form  = new CarsForm();
        $form->bind($cars);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($cars->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getCarsTable()->saveCars($form->getData());

                // Redirect to list of albums
                return $this->redirect()->toRoute('cars');
            }
        } 

        } else {
            echo 'ERROR!!!!';
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity() && $this->zfcUserAuthentication()->getIdentity()->getRole() == "admin") {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('cars');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->getCarsTable()->deleteCars($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('cars');
        }

        return array(
            'id'    => $id,
            'cars' => $this->getCarsTable()->getCars($id)
        );
    }
}
    public function getCarsTable()
    {
        if (!$this->carsTable) {
            $sm = $this->getServiceLocator();
            $this->carsTable = $sm->get('Cars\Model\CarsTable');
        }
        return $this->carsTable;
    }
}