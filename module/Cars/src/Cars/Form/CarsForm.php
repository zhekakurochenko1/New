<?php

namespace Cars\Form;

use Zend\Form\Form;

class CarsForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cars');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');


        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'artist',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Name_cars',
            ),
        ));
		
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'file',
                'id' => 'title',

                 //'multiple' => 'true',
            ),
            'options' => array(
                'label' => 'File',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Edit',
                'id' => 'submitbutton',
            ),
        ));
    }
}