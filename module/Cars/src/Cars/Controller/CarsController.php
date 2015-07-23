<?php
namespace Cars\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Cars\Model\Cars;
use Cars\Form\CarsForm;
use Zend\Validator;
use Zend\Validator\File\Size;
//use Zend\Mvc\Controller\AbstractActionController;
//use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Request;
use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\InputFilter\FileInput;


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
        $request = $this->getRequest();
        if ($request->isPost()) {   
            $cars = new Cars();
            $form->setInputFilter($cars->getInputFilter());
            $form->setData(array_merge($request->getPost()->toArray(), $request->getFiles()->toArray()));
//var_dump($form->isValid()); die;
            if ($form->isValid()) {
             //var_dump($form->getElement('image'));DIE;
             //$form->getElement('image')->setDestination(getcwd() . '/public/img');
                $fileName = $form->getData()['title']['name'];
                if (move_uploaded_file($form->getData()['title']['tmp_name'], getcwd() . '/public/img/' . $fileName)) {
        echo "Файл корректен и был успешно загружен.\n";
    } else {
        echo "Возможная атака с помощью файловой загрузки!\n";
    }

                $cars->exchangeArray($form->getData());
                //print_r($product); die;
                $this->getCarsTable()->saveCars($cars); 
                // Redirect to list of products
                return $this->redirect()->toRoute('cars');
            }
        }
        return array('form' => $form);
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