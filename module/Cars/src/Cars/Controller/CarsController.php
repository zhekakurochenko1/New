<?php
namespace Cars\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Cars\Model\Cars;
use Cars\Form\CarsForm;
use Zend\Validator;
use Zend\Validator\File\Size;
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
       'carss' => $this->getCarsTable()->fetchAll(),
        ));
    }

    public function presAction(){
        
        return new ViewModel(array(
            'carss' => $this->getCarsTable()->fetchAll()));
        $view -> SetTemplate('cars/cars/pres.phtml');
        //return view;


    }
//-----------------
    public function addAction()
    {
        if ($this->zfcUserAuthentication()->hasIdentity() && $this->zfcUserAuthentication()->getIdentity()->getRole() == "admin") {
        $form = new CarsForm();
        $request = $this->getRequest();
        if ($request->isPost()) {   
            $cars = new Cars();
            $form->setInputFilter($cars->getInputFilter());
            $form->setData(array_merge($request->getPost()->toArray(), $request->getFiles()->toArray()));
            if ($form->isValid()) {
                $fileName = $form->getData()['title']['name'];
                if (move_uploaded_file($form->getData()['title']['tmp_name'], getcwd() . '/public/img/' . $fileName)) {
        echo "Файл корректен и был успешно загружен.\n";
    } else {
        echo "Возможная атака с помощью файловой загрузки!\n";
    }           $cars->exchangeArray($form->getData());
                $this->getCarsTable()->saveCars($cars); 
                return $this->redirect()->toRoute('cars');
            }
        }
        return array('form' => $form);
    } else {
        $view = new ViewModel(array(
                'message' => 'Доступ закрыт!',
            ));
            $view->setTemplate('cars/error/access');
            return $view;
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
         
        }  else {
            $view = new ViewModel(array(
            'message' => 'Доступ закрыт!',
            ));
            $view->setTemplate('cars/error/access');
            return $view;
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

            // Redirect to list of cars
            return $this->redirect()->toRoute('cars');
        }
        return array(
            'id'    => $id,
            'cars' => $this->getCarsTable()->getCars($id)
        );
    } else {
         $view = new ViewModel(array(
                'message' => 'Доступ закрыт!',
            ));
            $view->setTemplate('cars/error/access');
            return $view;
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