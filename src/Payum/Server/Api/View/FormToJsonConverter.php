<?php
namespace Payum\Server\Api\View;

use Symfony\Component\Form\FormInterface;

class FormToJsonConverter
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function convertMeta(FormInterface $form)
    {
        $formView = $form->createView();

        $fields = array();
        foreach ($formView->children as $name => $child) {
            $fields[$name] = array(
                'default' => $child->vars['data'],
                'label' => $child->vars['label'],
                'required' => $child->vars['required'],
            );

            if (in_array('text', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'text';
            } elseif (in_array('checkbox', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'checkbox';
            } elseif (in_array('choice', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'choice';
                $fields[$name]['choices'] = $child->vars['choices'];
            } else {
                $fields[$name]['type'] = 'text';
            }
        }

        return $fields;
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function convertInvalid(FormInterface $form)
    {
        return array(
            'errors' => $form->getErrorsAsString(),
        );
    }
}